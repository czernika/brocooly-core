<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeRule extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:rule';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Rules';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Rules';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this
			->addArgument(
				'rule',
				InputArgument::REQUIRED,
				'Rule name',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'rule' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme validation rule\n",
		]);

		$class = $this->generateClassCap();

		$this->createMethod( $class );
		$this->createPassesMethod( $class );
		$this->createMethod( $class, 'message' );

		$this->createFile( $this->file );

		$io->success( 'Rule ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createPassesMethod( $class ) {
		$passesMethod = $this->createMethod( $class, 'passes' );
        $passesMethod->addParameter( 'attribute');
        $passesMethod->addParameter( 'value' );
	}

	/**
	 * @return object
	 */
	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( Rule::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( Rule::class );

		return $class;
	}

}
