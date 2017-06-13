<?php

namespace VBCraft\Controllers;

use Slim\Views\Twig as View, PDO as PDO, Twig_SimpleFilter as custFunc;
use \VBCraft\Classes\Rcon as Rcon;

use \PayPal\Api\Payer;
use \PayPal\Api\Item;
use \PayPal\Api\ItemList;
use \PayPal\Api\Details;
use \PayPal\Api\Amount;
use \PayPal\Api\Transaction;
use \PayPal\Api\RedirectUrls;
use \PayPal\Api\Payment;
use \PayPal\Api\PaymentExecution;


class Home extends Controller {

	public function index($request, $response) {

		if (isset($_SESSION['playerName'])) {
			unset($_SESSION['playerName']);
		}

		$database = $this->dbh;
		$data = new \stdClass;

		$query = $database->prepare("SELECT * FROM donorsTotal ORDER BY total DESC LIMIT 1");
		try {
			$query->execute();
		}
		catch(PDOException $e) {
			die($e->getMessage());
		}
		$result = $query->fetchAll();
		if (!empty($result)) {
			@$data->donors->top->name  = $result[0]['name'];
			@$data->donors->top->total = $result[0]['total'];
		}

		$query = $database->prepare("SELECT * from donors ORDER by purchase_date DESC limit 0, 6");
		try {
			$query->execute();
		}
		catch(PDOException $e) {
			die($e->getMessage());
		}
		$result = $query->fetchAll();
		$donors = [];

		if (!empty($result)) {
			foreach ($result as $donor) {
				$info = [
					"name"          => $donor["name"],
					"product"       => $donor["product"],
					"amount"        => $donor["productAmount"],
					"price"         => $donor["productPrice"],
					"purchase_date" => $donor["purchase_date"]
				];
				
				array_push($donors, $info);
			}
			@$data->donors->list = $donors;
		}

		$query = $database->prepare("SELECT * from shopsections");
		try {
			$query->execute();
		}
		catch(PDOException $e) {
			die($e->getMessage());
		}
		$result = $query->fetchAll();
		$sections = [];

		if (!empty($result)) {
			foreach ($result as $section) {
				$info = [
					"name"    => $section["name"],
					"display" => $section["display_name"]
				];
				
				array_push($sections, $info);

				$query = $database->prepare("SELECT * from shopitems WHERE section = :section");
				$query->bindValue(':section', $info['name']);
				try {
					$query->execute();
				}
				catch(PDOException $e) {
					die($e->getMessage());
				}
				$result = $query->fetchAll();
				$items = [];

				if (!empty($result)) {
					foreach ($result as $item) {
						$info = [
							"id" => $item["id"],
							"desc" => $item["_desc"],
							"name" => $item["display_name"],
							"icon" => $item["icon"],
							"price" => $item["price"]
						];
						array_push($items, $info);
					}
					@$data->options->{$section["name"]} = $items;
				}

			}
			@$data->sections = $sections;
		}

		$func = new custFunc('test', function ($string, $string2, $string3) {
			return $string." ".$string2." ".$string3;
		});
		$this->container->view->getEnvironment()->addFilter($func);

		$this->container->view->getEnvironment()->addGlobal('data', $data);

		return $this->view->render($response, "home.php");
	}

	public function checkout($request, $response) {
		$fields = $request->getParsedBody();

		if (!isset($_SESSION['playerName']) OR !isset($fields['itemID'])) {
			if (isset($_SESSION['playerName'])) unset($_SESSION['playerName']);
		   	if (isset($_SESSION['playerName'])) unset($_SESSION['boughtItem']);
		    if (isset($_SESSION['playerName'])) unset($_SESSION['itemAmount']);
		    if (isset($_SESSION['playerName'])) unset($_SESSION['itemPrice']);

			return $response->withRedirect('/');
		}

		$itemID = $fields['itemID'];

		$database = $this->dbh;
		$query = $database->prepare("SELECT * FROM shopitems");
		try {
            $query->execute();
        }
        catch(PDOException $e) {
            die($e->getMessage());
        }
        $shop_items = $query->fetchAll(PDO::FETCH_OBJ);
        $shop_item = $this->IDCheck($shop_items, "id", $itemID);
        if (!$shop_item) {
        	return $response->withRedirect('/');
        }
        
        $product  = $shop_item->display_name;
        $price    = $shop_item->price;
        $tax      = $shop_item->tax;
        $shipping = $shop_item->shipping;
        $amount   = $shop_item->amount;
        $total    = $price + $shipping;

        $_SESSION['itemCommand'] = str_replace("{price}", $total, str_replace("{product}", $product, str_replace("{amount}", $amount, str_replace("{player}", $_SESSION['playerName'], $shop_item->command))));
        $_SESSION['itemMessage'] = str_replace("{price}", $total, str_replace("{product}", $product, str_replace("{amount}", $amount, str_replace("{player}", $_SESSION['playerName'], $shop_item->message))));

        $_SESSION["boughtItem"]  = $product;
		$_SESSION["itemAmount"]  = $amount;
		$_SESSION['itemPrice']   = $total;

		$payer = new Payer();
		$payer->setPaymentMethod('paypal');

		$item = new Item();
		$item->setName($product)
			->setCurrency('USD')
			->setQuantity(1)
			->setPrice($price);

		$itemList = new ItemList();
		$itemList->setItems([$item]);

		$details = new Details();
		$details->setShipping($shipping)
				->setSubtotal($price);

		$amount = new Amount();
		$amount->setCurrency('USD')
			->setTotal($total)
			->setDetails($details);

		$transaction = new Transaction();
		$transaction->setAmount($amount)
			->setItemList($itemList)
			->setDescription('PayForSomething Payment')
			->setInvoiceNumber(uniqid());

		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl(SITE_URL . '/pay?success=true')
			->setCancelUrl(SITE_URL);

		$payment = new Payment();
		$payment->setIntent('sale')
			->setPayer($payer)
			->setRedirectUrls($redirectUrls)
			->setTransactions([$transaction]);

		try {
			$payment->create($this->paypal);
		} catch (Exception $e) {
			return $response->withRedirect('/');
		}

		$approvalUrl = $payment->getApprovalLink();
		return $response->withRedirect($approvalUrl);
	}

	public function pay($request, $response) {
		$fields = $request->getParams();
		$mcserver = $request->getAttribute('_MCSERVER');

		if (!isset($_SESSION['playerName'], $fields['success'], $fields['paymentId'], $fields['PayerID']) OR (bool)$fields['success'] === false) {
		    return $response->withRedirect('/');
		}

		$host = $mcserver['rcon']['host'];
		$port = $mcserver['rcon']['port'];
		$password = $mcserver['rcon']['pass'];
		 
		$rcon = new Rcon($host, $port, $password, 3);

		$paymentId = $fields['paymentId'];
		$PayerId   = $fields['PayerID'];

		$itemAmount = $_SESSION['itemAmount'];
		$boughtItem = $_SESSION['boughtItem'];
		$playerName = $_SESSION['playerName'];
		$itemPrice  = $_SESSION['itemPrice'];

		$payment = Payment::get($paymentId, $this->paypal);

		$execute = new PaymentExecution();
		$execute->setPayerId($PayerId);

		try {
		    $result = $payment->execute($execute, $this->paypal);
		} catch (Exception $e) {
		    return $response->withRedirect('/');
		}

		$database = $this->dbh;

		$query = $database->prepare("INSERT INTO donors (name, product, productAmount, productPrice) VALUES (:player,:item,:amount,:price)");
	    $query->bindValue(":player", $playerName);
	    $query->bindValue(":item", $boughtItem);
	    $query->bindValue(":amount", $itemAmount);
	    $query->bindValue(":price", $itemPrice);
	    try {
	        $query->execute();
	    }
	    catch(PDOException $e) {
	        die($e->getMessage());
	    }

	    $query = $database->prepare("SELECT name FROM donorsTotal WHERE name=:player");
	    $query->bindValue(":player", $playerName);
	    try {
	        $query->execute();
	    }
	    catch(PDOException $e) {
	        die($e->getMessage());
	    }

	    $row = $query->fetchAll();

	    if(!empty($row)) {
	        $querryBefore = $database->prepare("SELECT total FROM donorsTotal WHERE name=:player");
	        $querryBefore->bindValue(":player", $playerName);
	        try {
	            $querryBefore->execute();
	        }
	        catch(PDOException $e) {
	            die($e->getMessage());
	        }
	        $row = $querryBefore->fetchAll();

	        $before = $row[0]["total"];
	        $after = $before + $itemPrice;
	        echo $after;
	        $query = $database->prepare("UPDATE donorsTotal SET total='$after' WHERE name=:player");
	        $query->bindValue(":player", $playerName);
	        try {
	            $query->execute();
	        }
	        catch(PDOException $e) {
	            die($e->getMessage());
	        }

	    } else {
	        $query = $database->prepare("INSERT INTO donorsTotal (name, total) VALUES (:player,:price)");
	        $query->bindValue(":player", $playerName);
	        $query->bindValue(":price", $itemPrice);
	        try {
	            $query->execute();
	        }
	        catch(PDOException $e) {
	            die($e->getMessage());
	        }
	    }

	    if ($rcon->connect()) {
	        $rcon->send_command($_SESSION["itemCommand"]);
	        $rcon->send_command('tellraw @a '.$_SESSION["itemMessage"]);
	    }
	    return $response->withRedirect('/');
	}

	public function ver($request, $response) {
		$fields = $request->getParsedBody();
		if (!isset($fields['name'])) {
			return false;
		}

		$name = $fields['name'];
		$json = @file_get_contents("https://api.mojang.com/users/profiles/minecraft/$name");
		$id = json_decode($json);
		if (!$id) {
			return false;
		} else {
			if (isset($_SESSION['playerName'])) {
				unset($_SESSION['playerName']);
			}
			$_SESSION["playerName"] = $id->name;
			return $id->name;
		}
	}

	private function IDCheck($array, $key, $val) {
	    foreach ($array as $item) {
	        if (isset($item->$key) && $item->$key == $val) {
	            return $item;
	        }
	    }
	    return false;
	}
}
