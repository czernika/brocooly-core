<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Http\Controllers\BaseController;
use Brocooly\Http\Middleware\AbstractMiddleware;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeMiddleware extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:middleware';

	protected function configure(): void
    {
        $this
			->addArgument(
				'middleware',
				InputArgument::REQUIRED,
				'Middleware name',
			)
			->addOption(
				'base',
				'b',
				InputOption::VALUE_NONE,
				'Create base middleware',
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
		$name = $input->getArgument( 'middleware' );

		// Options
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
		$file->addComment( $this->className . " - custom theme middleware\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		if ( $base ) {
			$this->fileNamespace = 'Theme\Http\Middleware';
			$this->themeFileFolder = 'Http/Middleware';
		}

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( AbstractMiddleware::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractMiddleware::class );

		$this->createMethod( $class, 'handle' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Middleware ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
