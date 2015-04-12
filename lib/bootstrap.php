<?php

    require_once 'Config/config.php';

    require __DIR__.'/ActiveRecord/ActiveRecord.php';

	use FLaRM\FLaRM;

	class AutoLoader
	{
		public static function Load($class)
		{
			$file = FRAMEWORK_PATH.$class.'.php';
            echo $file . "<br />";
			if(is_readable($file))
			{
				require_once($file);
			}
			else
			{
				die('Requested class "'.$class.'" is missing. Execution stopped.');
			}
		}
	}

	spl_autoload_register('AutoLoader::Load');

    ActiveRecord\Config::initialize(function($cfg, $baseConfig){
        $cfg->set_model_directory('Models');
        $cfg->set_connections(array('development' => $baseConfig['driver'] . '://' . $baseConfig['user'] . ':' . $baseConfig['pass'] . '@' . $baseConfig['server'] . '/' . $baseConfig['database'] . ''));
    });

	$FLaRM = new FLaRM();
    $FLaRM->loadObject('\Router\Router', 'router');
    $FLaRM->loadObject('ActiveRecord\Connection', 'connection');
    $FLaRM->forceLoadObject('ActiveRecord\Connection', 'connection');
    $router = $FLaRM->getObject('router');
    $db = $FLaRM->getObject('connection');
    var_dump($router);
    var_dump($db);

