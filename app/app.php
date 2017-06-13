<?php

header('Content-Type: text/html; charset=utf-8');

session_start();

include_once __DIR__ . '/../vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$site_root = (!empty($_SERVER['HTTPS']) ? 'https':'http').'://'.$_SERVER['HTTP_HOST'];

define('SITE_URL', $site_root);

/*****************************************************************************************************

SANDBOX

$clientId = 'CLIENT TOKEN FOR SANDBOX';
$clientSecret = 'SECRET TOKEN FOR SANDBOX';


*****************************************************************************************************/

/*****************************************************************************************************

LIVE

$clientId = 'CLIENT TOKEN FOR LIVE';
$clientSecret = 'SECRET TOKEN FOR LIVE';


*****************************************************************************************************/

$clientId = 'CLIENT TOKEN';
$clientSecret = 'SECRET TOKEN';

$paypal = new \PayPal\Rest\ApiContext(
        new \PayPal\Auth\OAuthTokenCredential(
            $clientId,
            $clientSecret
        )
    );

/***

COMMENT THIS OUT WHEN YOU WANT TO GO LIVE, AND ACCEPT REAL MONEY

$paypal->setConfig(
    array(
        'mode' => 'live',
        'log.LogEnabled' => true,
        'log.FileName' => '../PayPal.log',
        'log.LogLevel' => 'DEBUG',
        'validation.level' => 'log',
        'cache.enabled' => true,
    )
);
***/

$store_host = 'database_host';
$store_dbuser = 'database_username';
$store_dbpass = 'database_password';
$store_dbname = 'vanillabuycraft';

$configDB = [
    'store' => [
        'host' => $store_host,
        'user' => $store_dbuser,
        'pass' => $store_dbpass,
        'name' => $store_dbname
    ]
        
];

$configServer = [
    'rcon' => [
        'host' => "SERVER IP",
        'pass' => "RCON PASSWORD",
        'port' => 25566 // RCON PORT (is not always 25566)
    ]
];


$settings = [
    'database'                          => $configDB,
    'paypal'                            => $paypal,
    'MCServer'                          => $configServer,
	'displayErrorDetails'               => true,
	'determineRouteBeforeAppMiddleware' => true,
	'addContentLengthHeader'            => false
];
$config = [
	'settings' => $settings
];

$app = new \Slim\App($config);

$container = $app->getContainer();

include_once __DIR__ . '/Middleware/TrailingSlashMiddleware.php';
include_once __DIR__ . '/containers/containers.php';
include_once __DIR__ . '/routes/routes.php';

$app->add(new VBCraft\Middleware\TrailingSlashMiddleware($container));