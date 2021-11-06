<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Mail\Mailable;
use Brocooly\Models\Users\User;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeModelRole extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:role';

	protected $fileNamespace = 'Theme\Models\Users';

	protected $themeFileFolder = 'Models/Users';

	protected function configure(): void
    {
        $this
			->addArgument(
				'role',
				InputArgument::REQUIRED,
				'Create new user role',
			)
			->addOption(
				'base',
				'b',
				InputOption::VALUE_NONE,
				'Define this as a base WordPress role',
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
		$name = $input->getArgument( 'role' );

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
		$file->addComment( $this->className . " - custom theme role\n" )
			->addComment( "! Register this class inside `users.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( User::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( User::class );

		$roleConstant = $class->addConstant( 'ROLE', Str::snake( $this->className ) );
		$roleConstant->addComment( "Role name\n" )
						->addComment( '@var string' );

		if ( ! $base ) {
			$label = Str::headline( $this->className );
			$labelMethod = $this->createMethod(
				$class,
				'label',
"return esc_html__( '{$label}', 'brocooly' );"
			);

			$labelMethod->addComment( "Return role name in human readable format\n" )
							->addComment( '@return string' )
							->setReturnType( 'string' );

			$buildMethod = $this->createMethod(
				$class,
				'capabilities',
'return $this->as( \'administrator\' );'
			);

			$buildMethod
				->addComment( 'Get user capabilities' )
				->addComment( "We will set same level of caps as admin has\n" )
				->addComment( '@return array' )
				->setReturnType( 'array' );
		}

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Custom role ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
