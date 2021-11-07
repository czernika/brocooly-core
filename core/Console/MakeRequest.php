<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Http\Controllers\BaseController;
use Brocooly\Http\Middleware\AbstractMiddleware;
use Brocooly\Http\Request\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeRequest extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:request';

	protected function configure(): void
    {
        $this
			->addArgument(
				'request',
				InputArgument::REQUIRED,
				'Request name',
			)
			->addOption(
				'base',
				'b',
				InputOption::VALUE_NONE,
				'Create base request',
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
		$name = $input->getArgument( 'request' );

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
		$file->addComment( $this->className . " - custom theme request\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		if ( $base ) {
			$this->fileNamespace = 'Theme\Http\Request';
			$this->themeFileFolder = 'Http/Request';
		}

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( Request::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( Request::class );

		if ( $base ) {
			$class->setAbstract();
		}

		$method = $this->createMethod( $class, 'rules' );

		$method->setReturnType( 'array' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Request ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
