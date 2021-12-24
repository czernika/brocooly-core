<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Providers\AbstractService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeProvider extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:provider';

	/**
	 * @inheritDoc
	 */
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
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'provider' );
		$base = $input->getOption( 'base' );

		if ( $base ) {
			$this->rootNamespace = 'Theme\Providers';
			$this->themeFileFolder = 'Providers';
		}

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme service provider\n",
			"! Register this class inside `config/app.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		$this->createMethod( $class, 'register' );
		$this->createMethod( $class, 'boot' );

		// Create file
		$this->createFile( $this->file );

		$io->success( 'Provider ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractService::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractService::class );

		return $class;
	}

}
