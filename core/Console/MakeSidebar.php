<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\UI\Menus\AbstractMenu;
use Brocooly\UI\Widgets\AbstractSidebar;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeSidebar extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:sidebar';

	protected $fileNamespace = 'Theme\UI\Widgets\Sidebars';

	protected $themeFileFolder = 'UI/Widgets/Sidebars';

	protected function configure(): void
    {
        $this
			->addArgument(
				'sidebar',
				InputArgument::REQUIRED,
				'Sidebar name',
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
		$name = $input->getArgument( 'sidebar' );

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
		$file->addComment( $this->className . " - custom theme sidebar\n" )
			->addComment( "! Register this class inside `widgets.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( AbstractSidebar::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractSidebar::class );

		$snakeName = Str::snake( $this->className );
		$panelConstant = $class->addConstant( 'SIDEBAR_ID', $snakeName );
		$panelConstant->addComment( "Sidebar location\n" )
						->addComment( "@var string" );

		$sidebarName = Str::headline( $this->className );
		$method = $this->createMethod(
			$class,
			'options',
"return [
	'name'        => esc_html__( '{$sidebarName} sidebar', 'brocooly' ),
	'description' => esc_html__( '{$sidebarName} description', 'brocooly' ),
];"
		);

		$method
			->addComment( "Get sidebar options\n" )
			->addComment( '@return array' )
			->setReturnType( 'array' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Sidebar ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
