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

$router[] = new Route('[<name [a-z0-9]*>]', function($presenter, $name, \Nette\Http\Request $request)
	use ($container) {

	$template = $presenter->createTemplate()->setFile(__DIR__ . '/templates/home.latte');

	if (empty($name)) {
		$story = Html::el()->setHtml("Odpočetfacky.com\n<br>")->add(
			Html::el('a')->setText('VYTVOŘIT')->href($request->getUrl()->getScriptPath() . 'create'));
	} else {
		$story = "Some $name story...";
	}

	$template->setParameters(array(
		'countdown' => (object) array(
			'days' => 0,
			'hours' => 0,
			'minutes' => 0,
			'seconds' => 3,
		),
		'reload' => false,
		'backgroundColor' => '000',
		'textColor' => 'fff',
		'story' => $story,
	));
	return $template;
});

$container->getService('application')->run();
