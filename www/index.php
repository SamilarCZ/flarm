<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require __DIR__ . '/.maintenance.php';

$container = require __DIR__ . '/../app/bootstrap.php';
try {
	$container->getByType('Nette\Application\Application')->run();
}catch (\Nette\DI\ServiceCreationException $e){
	if($e->getMessage() === 'Service of type Nette\Database\Context needed by App\Presenters\IndexPresenter::inject() not found. Did you register it in configuration file?') {
		\Tracy\Debugger::log($e);
		echo '<h1>Application STOPPED!!!</h1>You must setup the database connection first!!! Go to <strong>config.local.neon</strong> on section database and set there the right values please.!!!';
		echo '<h2>Example : </h2>';
		echo '<pre>';
		echo '
database:
	dsn: \'mysql:host=127.0.0.1;dbname=*****\'
	user: username
	password: password
	options:
		lazy: yes
';
		echo '</pre>';
	}
}
