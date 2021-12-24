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

	/**
	 * @inheritDoc
	 */
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
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'middleware' );
		$base = $input->getOption( 'base' );

		if ( $base ) {
			$this->rootNamespace   = 'Theme\Http\Middleware';
			$this->themeFileFolder = 'Http/Middleware';
		}

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme middleware\n",
		]);

		$class = $this->generateClassCap();

		$this->createMethod( $class, 'handle' );

		$this->createFile( $this->file );

		$io->success( 'Middleware ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractMiddleware::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractMiddleware::class );

		return $class;
	}

}
