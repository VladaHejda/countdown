<?php

use Nette\Application\Routers\Route;
use Nette\Database\Context;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;
$configurator->enableDebugger(__DIR__ . '/../log');
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->addConfig(__DIR__ . '/config/secure.neon', $configurator::AUTO);
$configurator->addConfig(__DIR__ . '/config/services.neon');
$container = $configurator->createContainer();

$router = $container->getService('router');

$router[] = new Route('[<name [a-z0-9]*>]', function($presenter, $name, Context $database) use ($container) {

	$template = $presenter->createTemplate();
	$days = $hours = $minutes = $seconds = 0;
	$showStory = false;

	try {
		if (empty($name)) {
			$seconds = $container->getParameters()['defaultSeconds'];
			$story = null;

		} else {
			$data = $database->fetch('SELECT story, expiration FROM countdown WHERE name = ?', $name);
			if (!$data) {
				throw new \InvalidArgumentException;
			}
			$story = $data->story;
			$now = new \DateTime;
			if ($now > $data->expiration) {
				$showStory = true;
			} else {
				$secondsShift = $data->expiration->format('U') - $now->format('U');
				$days = floor($secondsShift / (60 *60 *24));
				$interval = $now->diff($data->expiration);
				$hours = $interval->h;
				$minutes = $interval->i;
				$seconds = $interval->s;
			}
		}
		$template->setFile(__DIR__ . '/templates/countdown.latte');
	} catch (\InvalidArgumentException $e) {
		$template->setFile(__DIR__ . '/templates/404.latte');
	}

	$template->setParameters([
		'countdown' => (object) [
			'days' => $days,
			'hours' => $hours,
			'minutes' => $minutes,
			'seconds' => $seconds,
		],
		'reload' => false,
		'backgroundColor' => '000',
		'textColor' => 'fff',
		'defaultPage' => !$name,
		'showStory' => $showStory,
		'story' => $story,
	]);
	return $template;
});

$container->getService('application')->run();
