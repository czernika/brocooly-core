<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeHook extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:hook';

	protected $fileNamespace = 'Theme\Hooks';

	protected $themeFileFolder = 'Hooks';

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
	 * Execute method
	 *
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{

		$io = new SymfonyStyle( $input, $output );

		// Argument
		$name = $input->getArgument( 'hook' );

		// Options
		$isAction = $input->getOption( 'action' );
		$isFilter  = $input->getOption( 'filter' );

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
		$file->addComment( $this->className . " - custom theme hook\n" )
			->addComment( "! Register this class inside `app.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );

		$class = $namespace->addClass( $this->className );

		if ( $isFilter || $isAction ) {

			$hook = Str::snake( $this->className );
			$methodContent = "return '{$hook}';";

			if ( $isFilter ) {
				$filterMethod = $this->createMethod( $class, 'filter', $methodContent );
				$filterMethod
					->addComment( 'Filter hook name' )
					->addComment( "Pass correct hook name as for WordPress `add_filter()`\n" )
					->addComment( '@return string' )
					->setReturnType( 'string' );
			}

			if ( $isAction ) {
				$actionMethod = $this->createMethod( $class, 'action', $methodContent );
				$actionMethod
					->addComment( 'Action hook name' )
					->addComment( "Pass correct hook name as for WordPress `add_action()`\n" )
					->addComment( '@return string' )
					->setReturnType( 'string' );
			}

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

		} else {
			$hookMethod = $this->createMethod( $class, 'load' );
			$hookMethod
					->addComment( 'Hook function' )
					->addComment( "Hook itself. Call `add_action` or `add_filter` here\n" )
					->addComment( '@return void' )
					->setReturnType( 'void' );
		}

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Hook ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
