<?php

ini_set('max_execution_time', 3000);

$host = 'database_host';
$dbuser = 'database_username';
$dbpass = 'database_password';


try {
	$conn = new PDO('mysql:host='.$host.';', $dbuser, $dbpass);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
	file_put_contents('connection.errors.txt', date('Y-m-d H:i:s')."> ".$e->getMessage().PHP_EOL,FILE_APPEND);
}

$conn->query("CREATE DATABASE IF NOT EXISTS vanillabuycraft");

$conn = null;

$dbname = 'vanillabuycraft';

try {
	$conn = new PDO('mysql:host='.$host.';dbname='.$dbname, $dbuser, $dbpass);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$conn->exec("SET CHARACTER SET utf8");
}
catch(PDOException $e) {
	echo 'Connection failed: ' . $e->getMessage();
	file_put_contents('connection.errors.txt', date('Y-m-d H:i:s')."> ".$e->getMessage().PHP_EOL,FILE_APPEND);
}

$sql = "CREATE TABLE IF NOT EXISTS donors (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(255),
		product VARCHAR(255),
		productAmount VARCHAR(255),
		productPrice DECIMAL(19,2) NOT NULL,
		purchase_date TIMESTAMP
		)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS donorstotal (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(255),
		total DECIMAL(19,2)
		)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS shopsections (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		name TEXT,
		display_name TEXT
		)";
$conn->query($sql);

$sql = "CREATE TABLE IF NOT EXISTS shopitems (
		id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		amount INT(6) NOT NULL DEFAULT '1',
		name TEXT,
		display_name TEXT,
		_desc TEXT,
		icon TEXT,
		section TEXT,
		command TEXT,
		message TEXT,
		tax DECIMAL(19,2) NOT NULL DEFAULT '0.0',
		shipping DECIMAL(19,2) NOT NULL DEFAULT '0.0',
		price DECIMAL(19,2)
		)";
$conn->query($sql);

echo "Databases installed";