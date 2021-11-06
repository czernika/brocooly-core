<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Mail\Mailable;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeMail extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:mail';

	protected function configure(): void
    {
        $this
			->addArgument(
				'mail',
				InputArgument::REQUIRED,
				'Create new mailable class',
			)
			->addOption(
				'base',
				'b',
				InputOption::VALUE_NONE,
				'Create base mailable',
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
		$name = $input->getArgument( 'mail' );

		// Options
		$base = $input->getOption( 'base' );

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
		$file->addComment( $this->className . " - mailable class\n" )
			->addComment( "Used to send emails\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		if ( $base ) {
			$this->fileNamespace = 'Theme\UI\Mails';
			$this->themeFileFolder = 'UI/Mails';
		}

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( Mailable::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( Mailable::class );

		$this->createMethod( $class, '__construct' );

		$buildMethod = $this->createMethod(
			$class,
			'build',
'$this->subject = esc_html__( \'Email subject\', \'brocooly\' );
$this->message = \'Message or template\';'
		);

		$buildMethod
			->addComment( "Define email constants\n" )
			->addComment( 'You may set `$this->message` as simple string or template content with use of `$this->template()`' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Mailable ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
