<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Theme\Http\Controllers\AjaxController;
use Brocooly\Http\Controllers\BaseController;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeController extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:controller';

	/**
	 * @inheritDoc
	 */
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
				'ajax',
				'a',
				InputOption::VALUE_NONE,
				'Create ajax controller',
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
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'controller' );

		$options = [ 'invokable', 'resource', 'construct', 'base', 'ajax' ];
		foreach ( $options as $option ) {
			$this->$option = $input->getOption( $option );
		}

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme controller\n",
		]);

		if ( $this->base ) {
			$this->rootNamespace   = 'Theme\Http\Controllers';
			$this->themeFileFolder = 'Http/Controllers';
		}

		$class = $this->generateClassCap();

		$this->generateMethods( $class );

		$this->createFile( $this->file );

		$io->success( 'Controller ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function generateMethods( $class ) {
		if ( $this->base ) {
			$class->setAbstract();
		}

		if ( $this->ajax ) {
			$this->createMethod( $class, 'handle' );
		}

		if ( $this->construct ) {
			$this->createMethod( $class );
		}

		if ( $this->invokable ) {
			$this->createMethod( $class, '__invoke' );
		}

		if ( $this->resource ) {
			$this->createMethod( $class, 'index' );
			$this->createMethod( $class, 'single' );
		}
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file?->addNamespace( $this->rootNamespace );
		$class     = $namespace->addClass( $this->className );

		if ( $this->ajax ) {
			$namespace->addUse( AjaxController::class );
			$class->addExtend( AjaxController::class );
		} else {
			$namespace->addUse( BaseController::class );
			$class->addExtend( BaseController::class );
		}

		return $class;
	}

}
