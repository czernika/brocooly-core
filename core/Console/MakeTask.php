<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\UI\Menus\AbstractMenu;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeTask extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:task';

	protected $fileNamespace = 'Theme\Tasks';

	protected $themeFileFolder = 'Tasks';

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
	 * Execute method
	 *
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{

		$io = new SymfonyStyle( $input, $output );

		// Argument
		$name = $input->getArgument( 'task' );

		// Options
		$isConstruct = $input->getOption( 'construct' );

		$file = new \Nette\PhpGenerator\PhpFile();

		// Collect data
		$namespaces = explode( '/', $name );
		$origin     = count( $namespaces );
		$this->className  = end( $namespaces );

		if ( $origin > 1 ) {
			unset( $namespaces[ $origin - 1 ]);
		}

		$classNamespace = $origin > 1 ?
							'\\' . implode( '\\', $namespaces ) :
							'';

		$this->folderPath = $origin > 1 ?
			'/' . implode( '/', $namespaces ) :
			'';

		// Create file content
		$file->addComment( $this->className . " - task\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$class = $namespace->addClass( $this->className );

		if ( $isConstruct ) {
			$this->createMethod( $class, '__construct' );
		}

		$method = $this->createMethod( $class, 'run' );

		$method
			->addComment( "The only task method\n" )
			->addComment( 'Task HAS TO return something' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Task ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
