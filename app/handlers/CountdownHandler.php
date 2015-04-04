<?php

use Nette\Database\Context;
use Nette\Http\Response;
use NetteModule\MicroPresenter;

class CountdownHandler extends \Nette\Object
{

	/** @var int */
	private $defaultSeconds;

	/** @var Context */
	private $database;

	/** @var Response */
	private $response;


	public function __construct($defaultSeconds, Context $database, Response $response)
	{
		$this->defaultSeconds = $defaultSeconds;
		$this->database = $database;
		$this->response = $response;
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
		$prologue = $epilogue = null;

		try {
			if (empty($name)) {
				$seconds = $this->defaultSeconds;
				$defaultPage = true;

			} else {

				$data = $this->database->fetch('SELECT prologue, epilogue, expiration, background, color
					FROM countdown WHERE name = ?', $name);
				if (!$data) {
					throw new \InvalidArgumentException;
				}
				$background = $data->background;
				$color = $data->color;
				$now = new \DateTime;
				if ($now > $data->expiration) {
					$epilogue = $data->epilogue;
					$finished = true;

				} else {
					$prologue = $data->prologue;
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
			$finished = true;
			$this->response->setCode(404);
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
			'prologue' => $prologue,
			'epilogue' => $epilogue,
		]);

		return $template;
	}

}
