<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Http\Request\Request;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeRequest extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:request';

	/**
	 * @inheritDoc
	 */
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
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'request' );
		$base = $input->getOption( 'base' );


		if ( $base ) {
			$this->rootNamespace   = 'Theme\Http\Request';
			$this->themeFileFolder = 'Http/Request';
		}

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme request\n",
		]);

		$class = $this->generateClassCap();

		if ( $base ) {
			$class->setAbstract();
		}

		$rulesMethod = $this->createMethod( $class, 'rules' );
		$rulesMethod->setReturnType( 'array' );

		$this->createFile( $this->file );

		$io->success( 'Request ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	/**
	 * @return object
	 */
	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( Request::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( Request::class );

		return $class;
	}

}
