<?php

namespace VBCraft\Middleware;

class TrailingSlashMiddleware extends Middleware {
	
	function __invoke($request, $response, $next) {
	    $uri = $request->getUri();
	    $path = $uri->getPath();
	    if ($path != '/' && substr($path, -1) == '/') {
	        $uri = $uri->withPath(substr($path, 0, -1));
	        if($request->getMethod() == 'GET') {
	            return $response->withRedirect((string)$uri, 301);
	        }
	        else {
	            return $next($request->withUri($uri), $response);
	        }
	    }
	    return $next($request, $response);
	}
}