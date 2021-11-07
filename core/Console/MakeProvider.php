<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Http\Controllers\BaseController;
use Brocooly\Http\Middleware\AbstractMiddleware;
use Brocooly\Providers\AbstractService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeProvider extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:provider';

	protected function configure(): void
    {
        $this
			->addArgument(
				'provider',
				InputArgument::REQUIRED,
				'Provider name',
			)
			->addOption(
				'base',
				'b',
				InputOption::VALUE_NONE,
				'Create base service provider',
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
		$name = $input->getArgument( 'provider' );

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
		$file->addComment( $this->className . " - custom theme service provider\n" )
			->addComment( "! Register this class inside `app.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		if ( $base ) {
			$this->fileNamespace = 'Theme\Http\Providers';
			$this->themeFileFolder = 'Http/Providers';
		}

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( AbstractService::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractService::class );

		$this->createMethod( $class, 'register' );
		$this->createMethod( $class, 'boot' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Provider ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
