<?php

namespace VBCraft\Middleware;

class GlobalSetMiddleware extends Middleware {

	function __invoke($request, $response, $next) {

        $request = $request->withAttribute('_MCSERVER', $this->container->get('settings')->get('MCServer'));


	    $this->container->view->getEnvironment()->addGlobal('session', $_SESSION);
	    $this->container->view->getEnvironment()->addGlobal('get', $_GET);
	    $this->container->view->getEnvironment()->addGlobal('post', $_POST);
	    return $next($request, $response);
	}
}