<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Router\View;
use Brocooly\Support\Facades\File;
use Brocooly\UI\Menus\AbstractMenu;
use Brocooly\UI\Shortcodes\AbstractShortcode;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeShortcode extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:shortcode';

	protected $fileNamespace = 'Theme\UI\Shortcodes';

	protected $themeFileFolder = 'UI/Shortcodes';

	protected function configure(): void
    {
        $this
			->addArgument(
				'shortcode',
				InputArgument::REQUIRED,
				'Shortcode name',
			)
			->addOption(
				'view',
				null,
				InputOption::VALUE_REQUIRED,
				'Create view file for shortcode',
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
		$name = $input->getArgument( 'shortcode' );

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
		$file->addComment( $this->className . " - custom theme shortcode\n" )
			->addComment( "! Register this class inside `views.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( AbstractShortcode::class );
		$namespace->addUse( View::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractShortcode::class );

		$snakeName = Str::snake( $this->className );
		$panelConstant = $class->addConstant( 'SHORTCODE_ID', $snakeName );
		$panelConstant->addComment( "Shortcode tag to be searched in post content\n" )
						->addComment( "@var string" );

		$viewFile = 'path/to/shortcode.twig';
		if ( $view ) {
			$viewFile = $view;

			$path = BROCOOLY_THEME_PATH . '/resources/views/' . $view;
			$dir = Str::of( $path )->beforeLast( '/' );
			File::ensureDirectoryExists( $dir );
			File::put( $path, '{# Shortcode file #}' );
		}

		$method = $this->createMethod(
			$class,
			'render',
"\$example = false;
if ( isset( \$atts['example'] ) ) {
	\$example = sanitize_text_field( \$atts['example'] );
}

// ! shortcodes HAVE TO return something
return View::compile( {$viewFile}, compact( 'example' ) );"
		);

		$method
			->addComment( "Render shortcode\n" )
			->addComment( 'The callback function to run when the shortcode is found.' )
			->addComment( "! Function called by the shortcode should never produce output of any kind.\n" )
			->addComment( '@var array $atts | shortocde attributes.' )
			->addComment( '@example available on front as:' )
			->addComment( '```' )
			->addComment( '{% apply shortcodes %}' )
			->addComment( "[{$snakeName} example=\"value\"]" )
			->addComment( '{% endapply %}' )
			->addComment( '```' );

		$method->addParameter( 'atts', [] )
				->setType( 'array' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Shortcode ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
