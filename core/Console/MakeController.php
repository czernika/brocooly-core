<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Http\Controllers\BaseController;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeController extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:controller';

	protected function configure(): void
    {
        $this
			->addArgument(
				'controller',
				InputArgument::REQUIRED,
				'Controller name',
			)
			->addOption(
				'base',
				'b',
				InputOption::VALUE_NONE,
				'Create base controller',
			)
			->addOption(
				'invokable',
				'i',
				InputOption::VALUE_NONE,
				'Create invokable controller',
			)
			->addOption(
				'construct',
				'c',
				InputOption::VALUE_NONE,
				'Add construct method',
			)
			->addOption(
				'resource',
				'r',
				InputOption::VALUE_NONE,
				'Create controller and both methods for index and single requests',
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
		$name = $input->getArgument( 'controller' );

		// Options
		$invokable = $input->getOption( 'invokable' );
		$resource  = $input->getOption( 'resource' );
		$construct = $input->getOption( 'construct' );
		$base      = $input->getOption( 'base' );

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
		$file->addComment( $this->className . " - custom theme controller\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		if ( $base ) {
			$this->fileNamespace = 'Theme\Http\Controllers';
			$this->themeFileFolder = 'Http/Controllers';
		}

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( BaseController::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( BaseController::class );

		if ( $base ) {
			$class->setAbstract();
		}

		if ( $construct ) {
			$this->createMethod( $class, '__construct' );
		}

		if ( $invokable ) {
			$this->createMethod( $class, '__invoke' );
		}

		if ( $resource ) {
			$this->createMethod( $class, 'index' );
			$this->createMethod( $class, 'single' );
		}

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Controller ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
