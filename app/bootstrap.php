<?php

use Nette\Application\Routers\Route;
use Nette\Utils\Html;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;
$configurator->enableDebugger(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->addConfig(__DIR__ . '/config/secure.neon', $configurator::AUTO);
$configurator->addConfig(__DIR__ . '/config/services.neon');
$container = $configurator->createContainer();

$router = $container->getService('router');

$router[] = new Route('[<name [a-z0-9]*>]', function($presenter, $name) use ($container) {

	$template = $presenter->createTemplate()->setFile(__DIR__ . '/templates/countdown.latte');

	$template->setParameters([
		'countdown' => (object) [
			'days' => 0,
			'hours' => 0,
			'minutes' => 0,
			'seconds' => 3,
		],
		'reload' => false,
		'backgroundColor' => '000',
		'textColor' => 'fff',
		'defaultPage' => !$name,
		'story' => "Some $name story...",
	]);
	return $template;
});

$container->getService('application')->run();
