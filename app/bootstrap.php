<?php

use Nette\Application\Routers\Route;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;
$configurator->setDebugMode([]);
$configurator->enableDebugger(__DIR__ . '/../log', 'hejdav@centrum.cz');
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->addConfig(__DIR__ . '/config/secure.neon', $configurator::AUTO);
$configurator->addConfig(__DIR__ . '/config/services.neon');
$container = $configurator->createContainer();

$router = $container->getService('router');

$router[] = new Route('create', function($presenter) use ($container) {
	return $container->getByType('CreateHandler')->run($presenter);
});

$router[] = new Route('[<name [a-z0-9]*>]', function($presenter, $name) use ($container) {
	return $container->getByType('CountdownHandler')->run($presenter, $name);
});

$container->getService('application')->run();
