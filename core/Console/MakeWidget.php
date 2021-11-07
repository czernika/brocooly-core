<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Support\Facades\File;
use Brocooly\Support\Facades\Meta;
use Brocooly\UI\Menus\AbstractMenu;
use Brocooly\UI\Widgets\AbstractSidebar;
use Brocooly\UI\Widgets\AbstractWidget;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeWidget extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:widget';

	protected $fileNamespace = 'Theme\UI\Widgets';

	protected $themeFileFolder = 'UI/Widgets';

	protected function configure(): void
    {
        $this
			->addArgument(
				'widget',
				InputArgument::REQUIRED,
				'Widget name',
			)
			->addOption(
				'view',
				null,
				InputOption::VALUE_REQUIRED,
				'Create view file for widget',
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
		$name = $input->getArgument( 'widget' );

		$view = $input->getOption( 'view' );

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
		$file->addComment( $this->className . " - custom theme widget\n" )
			->addComment( "! Register this class inside `widgets.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( AbstractWidget::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractWidget::class );

		$snakeName = Str::snake( $this->className );
		$panelConstant = $class->addConstant( 'WIDGET_ID', $snakeName );
		$panelConstant->addComment( "Widget id\n" )
						->addComment( "@var string" );

		$widgetName = Str::headline( $this->className );

		$titleMethod = $this->createMethod(
			$class,
			'title',
"return esc_html__( 'Brocooly | {$widgetName}', 'brocooly' );"
		);

		$titleMethod
			->addComment( "Widget title\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );

		$descriptionMethod = $this->createMethod(
			$class,
			'description',
"return esc_html__( '{$widgetName} description', 'brocooly' );"
		);

		$descriptionMethod
			->addComment( "Widget description\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );

		$optionsMethod = $this->createMethod(
			$class,
			'options',
"return [
	Meta::text( 'title', esc_html__( 'Title', 'brocooly' ) ),
];"
		);

		$namespace->addUse( Meta::class );

		$viewFile = 'path/to/widget.twig';
		if ( $view ) {
			$viewFile = $view;

			$path = BROCOOLY_THEME_PATH . '/resources/views/' . $view;
			$dir = Str::of( $path )->beforeLast( '/' );
			File::ensureDirectoryExists( $dir );
			File::put( $path, '{# Widget file #}' );
		}

		$optionsMethod
			->addComment( "Widget options\n" )
			->addComment( '@return array' )
			->setProtected()
			->setReturnType( 'array' );

		$viewMethod = $this->createMethod(
			$class,
			'view',
"return {$viewFile};"
		);

		$viewMethod
			->addComment( "Widget view instance\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Widget ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
