<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Support\Facades\File;
use Brocooly\Support\Facades\Meta;
use Brocooly\UI\Blocks\AbstractBlock;
use Brocooly\UI\Menus\AbstractMenu;
use Brocooly\UI\Widgets\AbstractSidebar;
use Brocooly\UI\Widgets\AbstractWidget;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeGutenberg extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:block';

	protected $fileNamespace = 'Theme\UI\Blocks';

	protected $themeFileFolder = 'UI/Blocks';

	protected function configure(): void
    {
        $this
			->addArgument(
				'block',
				InputArgument::REQUIRED,
				'Gutenberg block name',
			)
			->addOption(
				'view',
				null,
				InputOption::VALUE_REQUIRED,
				'Create view file for block',
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
		$name = $input->getArgument( 'block' );
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
		$file->addComment( $this->className . " - custom theme block\n" )
			->addComment( "! Register this class inside `blocks.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( AbstractBlock::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractBlock::class );

		$snakeName = Str::snake( $this->className );

		$blockName = Str::headline( $this->className );

		$titleMethod = $this->createMethod(
			$class,
			'title',
"return esc_html__( '{$blockName} Gutenberg block', 'brocooly' );"
		);

		$titleMethod
			->addComment( "Block title\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );

		$optionsMethod = $this->createMethod(
			$class,
			'fields',
"return [
	Meta::text( 'example_text', esc_html__( 'Example text', 'brocooly' ) ),
];"
		);

		$namespace->addUse( Meta::class );

		$viewFile = 'path/to/block.twig';
		if ( $view ) {
			$viewFile = $view;

			$path = BROCOOLY_THEME_PATH . '/resources/views/' . $view;
			$dir = Str::of( $path )->beforeLast( '/' );
			File::ensureDirectoryExists( $dir );
			File::put( $path, '{# Gutenberg block file #}' );
		}

		$optionsMethod
			->addComment( "Block fields\n" )
			->addComment( '@return array' )
			->setProtected()
			->setReturnType( 'array' );

		$viewMethod = $this->createMethod(
			$class,
			'view',
"return {$viewFile};"
		);

		$viewMethod
			->addComment( "Block view file\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Gutenberg block ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
