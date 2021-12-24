<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeTask extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:task';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Tasks';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Tasks';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this
			->addArgument(
				'task',
				InputArgument::REQUIRED,
				'Task name',
			)
			->addOption(
				'construct',
				'c',
				InputOption::VALUE_NONE,
				'Create construct method for task',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name        = $input->getArgument( 'task' );
		$isConstruct = $input->getOption( 'construct' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - task\n",
		]);

		$class = $this->generateClassCap();

		if ( $isConstruct ) {
			$this->createMethod( $class );
		}
		$this->createRunMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Task ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createRunMethod( $class ) {
		$method = $this->createMethod( $class, 'run' );

		$method
			->addComment( "The only task method\n" )
			->addComment( 'Task HAS TO return something' );
	}

}
