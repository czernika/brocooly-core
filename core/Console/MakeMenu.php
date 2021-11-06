<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\UI\Menus\AbstractMenu;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeMenu extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:menu';

	protected $fileNamespace = 'Theme\UI\Menus';

	protected $themeFileFolder = 'UI/Menus';

	protected function configure(): void
    {
        $this
			->addArgument(
				'menu',
				InputArgument::REQUIRED,
				'Menu location name',
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
		$name = $input->getArgument( 'menu' );

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
		$file->addComment( $this->className . " - custom theme menu\n" )
			->addComment( "! Register this class inside `menus.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( AbstractMenu::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractMenu::class );

		$panelConstant = $class->addConstant( 'LOCATION', Str::snake( $this->className ) );
		$panelConstant->addComment( "Menu location\n" )
						->addComment( "@var string" );

		$menuName = Str::headline( $this->className );
		$method = $this->createMethod(
			$class,
			'label',
"return esc_html__( '{$menuName}', 'brocooly' );"
		);

		$method
			->addComment( "Get menu label in admin area\n" )
			->addComment( '@return string' )
			->setReturnType( 'string' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Menu ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
