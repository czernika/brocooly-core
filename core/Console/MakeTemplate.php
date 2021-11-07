<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Support\Facades\File;
use Illuminate\Support\Str;
use Nette\PhpGenerator\Literal;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Theme\Models\WP\Post;

class MakeTemplate extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:template';

	protected $fileNamespace = 'Theme\UI\Templates';

	protected $themeFileFolder = 'UI/Templates';

	protected function configure(): void
    {
        $this
			->addArgument(
				'template',
				InputArgument::REQUIRED,
				'Template name',
			)
			->addOption(
				'post_type',
				'p',
				InputOption::VALUE_OPTIONAL,
				'Link to post type',
			)
			->addOption(
				'view',
				null,
				InputOption::VALUE_REQUIRED,
				'Create view file for template',
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
		$name = $input->getArgument( 'template' );
		$postType = $input->getOption( 'post_type' );

		$file = new \Nette\PhpGenerator\PhpFile();

		$view = $input->getOption( 'view' );

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
		$file->addComment( $this->className . " - custom theme template\n" )
			->addComment( "! Register this class inside `views.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );

		$class = $namespace->addClass( $this->className );

		if ( $view ) {
			$path = BROCOOLY_THEME_PATH . '/resources/views/' . $view;
			$dir = Str::of( $path )->beforeLast( '/' );
			File::ensureDirectoryExists( $dir );
			File::put( $path, '{# Template file #}' );
		}

		if ( null === $postType ) {
			$postTypes = [ new Literal( 'Post::POST_TYPE' ) ];
			$namespace->addUse( Post::class );
		} else {
			$postTypeSlug = Str::of( $postType )->after( '/' ) . '::POST_TYPE';
			$postTypes = [ new Literal( $postTypeSlug ) ];

			$postTypeClassName = 'Theme\Models\\' . Str::replace( '/', '\\', $postType );

			$namespace->addUse( $postTypeClassName );
		}

		$slugConstant = $class->addConstant( 'SLUG', Str::snake( $this->className ) );
		$slugConstant->addComment( "Template slug\n" )
						->addComment( '@var string' );

		$postTypesProperty = $class->addProperty( 'postTypes', $postTypes )
						->setType( 'array' )
						->addComment( "Template post type\n" )
						->addComment( '@var array' );

		$templateLabel = Str::headline( $this->className );
$content = "return esc_html__( 'Template: {$templateLabel}', 'brocooly' );";
		$labelMethod = $this->createMethod( $class, 'label', $content );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Template ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
