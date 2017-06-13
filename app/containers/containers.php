<?php


$container['view'] = function ($container) {
	$view = new \Slim\Views\Twig("../app/views", [
		"cache" => false,
	]);
	
	$view->addExtension(new \Slim\Views\TwigExtension(
		$container->router,
		$container->request->getUri()
	));
	$view->addExtension(new Twig_Extension_Debug());

	$twig = $view->getEnvironment();
	return $view;
};

$container['home'] = function ($container) {
	return new \VBCraft\Controllers\Home($container);
};