<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Models\Taxonomy;
use Brocooly\Support\Facades\Meta;
use Nette\PhpGenerator\Literal;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Theme\Models\WP\Post;

class MakeModelTaxonomy extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:taxonomy';

	protected $fileNamespace = 'Theme\Models';

	protected $themeFileFolder = 'Models';

	protected function configure(): void
    {
        $this
			->addArgument(
				'taxonomy',
				InputArgument::REQUIRED,
				'Create custom taxonomy',
			)
			->addOption(
				'post_type',
				'p',
				InputOption::VALUE_OPTIONAL,
				'Link to post type',
			)
			->addOption(
				'meta',
				'm',
				InputOption::VALUE_NONE,
				'Add meta-fields method',
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
		$name  = $input->getArgument( 'taxonomy' );

		// Options
		$meta     = $input->getOption( 'meta' );
		$postType = $input->getOption( 'post_type' );

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
		$file->addComment( $this->className . ' - custom taxonomy' )
			->addComment( "! Register this class inside `app.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( Meta::class )
					->addUse( Taxonomy::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( Taxonomy::class );

		$taxonomyConstant = $class->addConstant( 'TAXONOMY', Str::snake( $this->className ) );
		$taxonomyConstant->addComment( "Taxonomy slug\n" )
						->addComment( '@var string' );

		$webUrlProperty = $class->addProperty( 'webUrl', Str::snake( $this->className ) )
							->setType( 'string' )
							->setPrivate()
							->addComment( 'Web URL' )
							->addComment( "Publicly accessible name\n" )
							->addComment( '@var string' );

		if ( null === $postType ) {
			$postTypeSlug = new Literal( 'Post::POST_TYPE' );

			$namespace->addUse( Post::class );
		} else {
			$postTypeSlug = Str::of( $postType )->after( '/' ) . '::POST_TYPE';
			$postTypeSlug = new Literal( $postTypeSlug );

			$postTypeClassName = 'Theme\Models\\' . Str::replace( '/', '\\', $postType );

			$namespace->addUse( $postTypeClassName );
		}

		$postTypeProperty = $class->addProperty( 'postTypes', $postTypeSlug )
			->setProtected()
			->setStatic()
			->addComment( 'Post type related to this taxonomy' )
			->addComment( "Same as for `register_taxonomy()`\n" )
			->addComment( '@var array|string' );

		$taxonomyLabel       = Str::headline( $this->className );
		$pluralTaxonomyLabel = Str::plural( $taxonomyLabel );

		// protected options()
$optionsContent = "return [
	'labels'            => [
		'name'          => esc_html__( '{$pluralTaxonomyLabel}', 'brocooly' ),
		'singular_name' => esc_html__( '{$taxonomyLabel}', 'brocooly' ),
		'menu_name'     => esc_html__( '{$pluralTaxonomyLabel}', 'brocooly' ),
		'add_new'       => esc_html__( 'Add New {$taxonomyLabel}', 'brocooly' ),
	],
	'public'            => true,
	'show_in_menu'      => true,
	'show_ui'           => true,
	'hierarchical'      => true, // false for tags.
	'show_in_rest'      => true,
	'show_admin_column' => true,
	'rewrite'           => [
		'slug' => \$this->webUrl,
	],
];";

		$optionsMethod = $this->createMethod( $class, 'options', $optionsContent );

		$optionsMethod
			->setProtected()
			->addComment( 'Taxonomy register options' )
			->addComment( "Same as for `register_taxonomy()`\n" )
			->addComment( '@return array' )
			->setReturnType( 'array' );

	if ( $meta ) {
 	// protected fields()
$fieldsContent = "\$this->createFields(
	'container_id',
	esc_html__( 'Container label', 'brocooly' ),
	[
		Meta::text( 'example_meta', esc_html__( 'Example meta', 'brocooly' ) ),
	],
);";

		$fieldsMethod = $this->createMethod( $class, 'fields', $fieldsContent );
		$fieldsMethod
			->setProtected()
			->addComment( "Taxonomy metaboxes\n" )
			->addComment( '@return void' )
			->setReturnType( 'void' );

	}

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Custom taxonomy ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
