<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Http\IRequest;
use Nette\Application\Request;
use Nette\Http\Url;

/**
 * FLaRM Router factory which extends default Nette RouterFactory, but this one define default APP routes
 * and sets default presenter to IndexPresenter.
 * TODO: routes.neon - ability to directly create routes in this via configuration in NEON file (basics)
 */

/**
 * Router factory.
 */
class RouterFactory implements Nette\Application\IRouter{

	function match(IRequest $httpRequest){}

	function constructUrl(Request $appRequest, Url $refUrl){}

    public static function createRoutes(){
		$router = new Nette\Application\Routers\RouteList();
		$router[] = new Route('<presenter>[/<action>[/<id>]]', ['presenter' => 'Index', 'action' => 'index']);
        return $router;
    }

}
