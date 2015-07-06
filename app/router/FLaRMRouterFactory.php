<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * FLaRM Router factory which extends default Nette RouterFactory, but this one define default APP routes
 * and sets default presenter to IndexPresenter.
 * TODO: routes.neon - ability to directly create routes in this via configuration in NEON file (basics)
 */
class FLaRMRouterFactory extends RouterFactory
{
    protected $flarmRouter;

    public function __construct(){
        $this->flarmRouter = new RouteList();
        $this->flarmRouter = new Route('/', 'Index:index');
        $this->flarmRouter = new Route('<presenter>/<action>[/<id>]', 'Index:index');
        parent::createRouter($this);
	}
}
