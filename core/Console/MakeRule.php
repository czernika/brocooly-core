<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Http\Request\Request;
use Illuminate\Contracts\Validation\Rule;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeRule extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:rule';

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
	 * Execute method
	 *
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{

		$io = new SymfonyStyle( $input, $output );

		// Argument
		$name = $input->getArgument( 'rule' );

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
		$file->addComment( $this->className . " - custom theme validation rule\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( Rule::class );

		$class = $namespace->addClass( $this->className );
		$class->addImplement( Rule::class );

		$constructMethod = $this->createMethod( $class, '__construct' );
		$passesMethod    = $this->createMethod( $class, 'passes' );
        $passesMethod->addParameter( 'attribute')
                        ->addParameter( 'value' );
		$messagesMethod  = $this->createMethod( $class, 'message' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Rule ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
