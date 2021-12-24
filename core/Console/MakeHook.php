<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeHook extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:hook';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Hooks';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Hooks';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this
			->addArgument(
				'hook',
				InputArgument::REQUIRED,
				'Hook name',
			)
			->addOption(
				'action',
				'a',
				InputOption::VALUE_NONE,
				'Define this hook as an action',
			)
			->addOption(
				'filter',
				'f',
				InputOption::VALUE_NONE,
				'Define this hook as a filter',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'hook' );

		$isAction = $input->getOption( 'action' );
		$isFilter = $input->getOption( 'filter' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme hook\n",
			"! Register this class inside `config/app.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		if ( $isFilter && $isAction ) {
			$io->caution( 'Both `filter` and `action` flags cannot be used together' );
			return CreateClassCommand::FAILURE;
		}

		if ( $isFilter || $isAction ) {
			if ( $isFilter ) {
				$this->createFilterMethod( $class );
			}

			if ( $isAction ) {
				$this->createActionMethod( $class );
			}

			$this->createHookMethodWithFilter( $class, $isFilter );
		} else {
			$this->createHookMethod( $class );
		}

		$this->createFile( $this->file );

		$io->success( 'Hook ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createHookMethodContent() {
		return "return '{$this->snakeCaseClassName}';";
	}

	private function createHookMethodWithFilter( $class, $isFilter ) {
		$hookContent = '//...';
		if ( $isFilter ) {
			$hookContent = 'return $something;';
		}

		$hookMethod = $this->createMethod( $class, 'hook', $hookContent );
		$hookMethod
				->addComment( 'Hook callback function' )
				->addComment( "Here you may set any action\n" );

		if ( $isFilter ) {
			$hookMethod
				->addComment( "! Don't forget: filters HAVE TO return something\n" )
				->addParameter( 'something' );
		};
	}

	private function createActionMethod( $class ) {
		$actionMethod = $this->createMethod( $class, 'action', $this->createHookMethodContent() );
		$actionMethod
			->addComment( 'Action hook name' )
			->addComment( "Pass correct hook name as for WordPress `add_action()`\n" )
			->addComment( '@return string' )
			->setReturnType( 'string' );
	}

	private function createFilterMethod( $class ) {
		$filterMethod = $this->createMethod( $class, 'filter', $this->createHookMethodContent() );
		$filterMethod
			->addComment( 'Filter hook name' )
			->addComment( "Pass correct hook name as for WordPress `add_filter()`\n" )
			->addComment( '@return string' )
			->setReturnType( 'string' );
	}

	private function createHookMethod( $class ) {
		$hookMethod = $this->createMethod( $class, 'load' );
			$hookMethod
					->addComment( 'Hook function' )
					->addComment( "Hook itself. Call `add_action` or `add_filter` here\n" )
					->addComment( '@return void' )
					->setReturnType( 'void' );
	}

}
