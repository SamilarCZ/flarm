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

/**
 * Router factory.
 */
class RouterFactory implements Nette\Application\IRouter{

    /**
     * @param $router RouteList
     * @return array|RouteList
     */
    public static function createRouter($router){
        $router[] = new Route('index.php', 'Index:index', Route::ONE_WAY);
        $router[] = new Route('<action>', 'Index:index');
        return $router;
    }

    function match(Nette\Http\IRequest $httpRequest){ }

    function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl) { }
}
