<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Models\Users\User;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModelRole extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:role';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Models\Users';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Models/Users';

	/**
	 * @inheritDoc
	 */
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
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'role' );
		$base = $input->getOption( 'base' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme role\n",
			"! Register this class inside `config/users.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		$this->createRoleConstant( $class );

		if ( ! $base ) {
			$this->createLabelMethod( $class );
			$this->createBuildMethod( $class );
		}

		$this->createFile( $this->file );

		$io->success( 'Custom role ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createRoleConstant( $class ) {
		$roleConstant = $class->addConstant( 'ROLE', $this->snakeCaseClassName );
		$roleConstant->addComment( "Role name\n" )
						->addComment( '@var string' );
	}

	private function createLabelMethod( $class ) {
		$label = Str::headline( $this->className );
			$labelMethod = $this->createMethod(
				$class,
				'label',
"return esc_html__( '{$label}', 'brocooly' );"
			);

			$labelMethod->addComment( "Return role name in human readable format\n" )
							->addComment( '@return string' )
							->setReturnType( 'string' );
	}

	private function createBuildMethod( $class ) {
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

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( User::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( User::class );

		return $class;
	}

}
