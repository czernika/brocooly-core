<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Mail\Mailable;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMail extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:mail';

	/**
	 * @inheritDoc
	 */
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
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'mail' );
		$base = $input->getOption( 'base' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - mailable class\n",
			"Used to send emails\n",
		]);

		if ( $base ) {
			$this->rootNamespace   = 'Theme\UI\Mails';
			$this->themeFileFolder = 'UI/Mails';
		}

		$class = $this->generateClassCap();

		$this->createMethod( $class );
		$this->createBuildMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Mailable model ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( Mailable::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( Mailable::class );

		return $class;
	}

	private function createBuildMethod( $class ) {
		$buildMethod = $this->createMethod(
			$class,
			'build',
'$this->subject = esc_html__( \'Email subject\', \'brocooly\' );
$this->message = \'Message or template\';'
		);

		$buildMethod
			->addComment( "Define email constants\n" )
			->addComment( 'You may set `$this->message` as simple string or template content with use of `$this->template()`' );
	}

}
