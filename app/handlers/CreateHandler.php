<?php

use Nette\Bridges\FormsLatte\FormMacros;
use Nette\Database\Context;
use Nette\Forms\Container;
use \Nette\Forms\Form;
use NetteModule\MicroPresenter;
use Tracy\Debugger;

class CreateHandler extends \Nette\Object
{

	/** @var array */
	private static $reservedWords = ['create'];

	/** @var Context */
	private $database;

	/** @var MicroPresenter */
	private $presenter;


	public function __construct(Context $database)
	{
		$this->database = $database;
	}


	/**
	 * @param MicroPresenter $presenter
	 * @return \Nette\Application\UI\ITemplate
	 */
	public function run(MicroPresenter $presenter)
	{
		$this->presenter = $presenter;
		$template = $presenter->createTemplate();
		$template->setFile(__DIR__ . '/../templates/create.latte');
		FormMacros::install($template->getLatte()->getCompiler());

		$createForm = $this->createCreateForm();
		$createForm->fireEvents();

		$template->setParameters([
			'page' => 'create',
			'createForm' => $createForm,
		]);

		if ($createForm->isSuccess()) {
			$countdownName = $this->createCountdown($createForm->getValues());
			return $presenter->redirectUrl($countdownName);
		}

		return $template;
	}


	private function createCountdown(\stdClass $values)
	{
		$dateTime = $values->dateTime;

		do {
			try {
				$name = \Nette\Utils\Random::generate(5);
				if (in_array($name, static::$reservedWords, true)) {
					continue;
				}
				$this->database->query('INSERT INTO countdown ?', [
					'name' => $name,
					'prologue' => $values->prologue ?: null,
					'epilogue' => $values->epilogue,
					'expiration' => \Nette\Utils\DateTime::from("{$dateTime->year}-{$dateTime->month}-{$dateTime->day} {$dateTime->hour}:{$dateTime->minute}:{$dateTime->second}"),
					'background' => ltrim($values->backgroundColor, '#'),
					'color' => ltrim($values->textColor, '#'),
				]);
				break;
			} catch (\Nette\Database\UniqueConstraintViolationException $e) {
				Debugger::log($e, Debugger::ERROR);
			}
		} while (1);

		return $name;
	}


	private function createCreateForm()
	{
		$form = new Form;

		$form->addText('prologue', 'Text během odpočtu:');

		$this->addDateTimeContainer($form->addContainer('dateTime'));

		$form->addText('textColor', 'Barva textu:')
			->setType('color')
			->setRequired('Vyplň barvu textu.')
			->setDefaultValue('#ffffff')
			->addRule(Form::PATTERN, $colorError = 'Vyplň barvu jako # následující 6-ti hexadecimálními znaky.',
				$colorPattern = '#[a-f0-9]{6}');

		$form->addText('backgroundColor', 'Barva pozadí:')
			->setType('color')
			->setRequired('Vyplň barvu pozadí.')
			->setDefaultValue('#000000')
			->addRule(Form::PATTERN, $colorError, $colorPattern);

		$form->addText('epilogue', 'Text po vypršení:')
			->setRequired('Vyplň text.');

		$form->addSubmit('create', 'Vytvořit');

		return $form;
	}


	private function addDateTimeContainer(Container $container)
	{
		$now = new DateTime;

		$container->addText('year')
			->setType('number')
			->setRequired($fillDateError = 'Vyplň datum a čas vypršení.')
			->setDefaultValue($now->format('Y'))
			->addRule(Form::INTEGER, 'Vyplň správně rok.')
			->addRule(Form::RANGE, 'Vyplň správně rok.', [(int) date('Y'), null]);

		$container->addText('month')->setType('number')
			->setRequired($fillDateError)
			->setDefaultValue($now->format('n'))
			->addRule(Form::INTEGER, 'Vyplň správně měsíc.')
			->addRule(Form::RANGE, 'Vyplň správně měsíc.', [1, 12]);

		$container->addText('day')
			->setType('number')
			->setRequired($fillDateError)
			->setDefaultValue($now->format('j'))
			->addRule(Form::INTEGER, 'Vyplň správně den.')
			->addRule(Form::RANGE, 'Vyplň správně den.', [1, 31]);

		$container->addText('hour')
			->setType('number')
			->setRequired($fillDateError)
			->setDefaultValue((int) $now->format('H') +1) // plus one hour to current time
			->addRule(Form::INTEGER, 'Vyplň správně hodiny.')
			->addRule(Form::RANGE, 'Vyplň správně hodiny.', [0, 23]);

		$container->addText('minute')
			->setType('number')
			->setRequired($fillDateError)
			->setDefaultValue($now->format('i'))
			->addRule(Form::INTEGER, 'Vyplň správně minuty.')
			->addRule(Form::RANGE, 'Vyplň správně minuty.', [0, 59]);

		$container->addText('second')
			->setType('number')
			->setRequired($fillDateError)
			->setDefaultValue($now->format('s'))
			->addRule(Form::INTEGER, 'Vyplň správně vteřiny.')
			->addRule(Form::RANGE, 'Vyplň správně vteřiny.', [0, 59]);

		$container->onValidate[] = function(Container $container) {
			$values = $container->getValues();
			if (date('Y/n/j/H:i:s') >= "{$values->year}/{$values->month}/{$values->day}/{$values->hour}:{$values->minute}:{$values->second}") {
				$container->getComponent('day')->addError('Datum musí být v budoucnosti.');
			}
		};

		return $container;
	}

}
