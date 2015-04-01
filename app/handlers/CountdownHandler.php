<?php

use Nette\Database\Context;
use NetteModule\MicroPresenter;

class CountdownHandler extends \Nette\Object
{

	/** @var int */
	private $defaultSeconds;

	/** @var Context */
	private $database;


	public function __construct($defaultSeconds, Context $database)
	{
		$this->defaultSeconds = $defaultSeconds;
		$this->database = $database;
	}


	/**
	 * @param MicroPresenter $presenter
	 * @param $name
	 * @return \Nette\Application\UI\ITemplate
	 */
	public function run(MicroPresenter $presenter, $name)
	{
		$template = $presenter->createTemplate();
		$days = $hours = $minutes = $seconds = 0;
		$finished = $defaultPage = false;
		$background = '000000';
		$color = 'ffffff';

		try {
			if (empty($name)) {
				$seconds = $this->defaultSeconds;
				$defaultPage = true;
				$story = null;

			} else {

				$data = $this->database->fetch('SELECT story, expiration, background, color
					FROM countdown WHERE name = ?', $name);
				if (!$data) {
					throw new \InvalidArgumentException;
				}
				$story = $data->story;
				$background = $data->background;
				$color = $data->color;
				$now = new \DateTime;
				if ($now > $data->expiration) {
					$finished = true;

				} else {
					$secondsShift = $data->expiration->format('U') - $now->format('U');
					$days = floor($secondsShift / (60 *60 *24));
					$interval = $now->diff($data->expiration);
					$hours = $interval->h;
					$minutes = $interval->i;
					$seconds = $interval->s;
				}
			}
			$template->setFile(__DIR__ . '/../templates/countdown.latte');

		} catch (\InvalidArgumentException $e) {
			$template->setFile(__DIR__ . '/../templates/404.latte');
		}

		$template->setParameters([
			'countdown' => (object) [
				'days' => $days,
				'hours' => $hours,
				'minutes' => $minutes,
				'seconds' => $seconds,
			],
			'backgroundColor' => $background,
			'textColor' => $color,
			'defaultPage' => $defaultPage,
			'finished' => $finished,
			'story' => $finished ? $story : null,
		]);

		return $template;
	}

}
