<?php

namespace VBCraft\Controllers;

use PDO as PDO;

class Controller {
	protected $container;
	protected $dbh_forums;
	protected $dbh_market;
	protected $paypal;

	public function __construct($container) {
		$this->container = $container;

		$db = $container->get('settings')['database'];
		$this->paypal = $container->get('settings')['paypal'];

	    $store = new PDO("mysql:host=".$db['store']['host'].";dbname=".$db['store']['name'],$db['store']['user'],$db['store']['pass']);
	    $store->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	    $store->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

		$this->dbh = $store;
	}
	public function __get($prop) {
		if ($this->container->{$prop}) {
			return $this->container->{$prop};
		}
	}
}