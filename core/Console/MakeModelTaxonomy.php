<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Theme\Models\WP\Post;
use Illuminate\Support\Str;
use Brocooly\Models\Taxonomy;
use Nette\PhpGenerator\Literal;
use Brocooly\Support\Facades\Meta;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModelTaxonomy extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:taxonomy';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Models';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Models';

	/**
	 * Post types attached to taxonomy
	 *
	 * @var array
	 */
	private array $postTypes = [];

	/**
	 * Post type name defined by user
	 *
	 * @var string|null
	 */
	private ?string $postType = null;

	/**
	 * Post type class name defined by user
	 *
	 * @var string|null
	 */
	private ?string $postTypeClassName = null;

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this->addArgument(
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
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name  = $input->getArgument( 'taxonomy' );

		$meta           = $input->getOption( 'meta' );
		$this->postType = $input->getOption( 'post_type' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . ' - custom taxonomy',
			"! Register this class inside `config/app.php` file to have effect\n",
			"! It is recommended to flush permalinks\n",
		]);

		$class = $this->generateClassCap();

		if ( $this->postTypeClassName && ! class_exists( $this->postTypeClassName ) ) {
			$io->warning( 'Model class ' . $this->postTypeClassName . ' doesn\'t exists' );
		}

		$this->createTaxonomyConstant( $class );
		$this->createWebUrlProperty( $class );
		$this->createOptionsMethod( $class );
		$this->createPostTypesProperty( $class );

		if ( $meta ) {
			$this->createFieldsMethod( $class );
		}

		$this->createFile( $this->file );

		$io->success( 'Custom taxonomy ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( Taxonomy::class )
				->addUse( Meta::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( Taxonomy::class );

		if ( null === $this->postType ) {
			$this->postTypes = [ new Literal( 'Post::POST_TYPE' ) ];
			$namespace->addUse( Post::class );
		} else {
			$postTypeSlug = Str::of( $this->postType )->after( '/' ) . '::POST_TYPE';
			$this->postTypes = [ new Literal( $postTypeSlug ) ];
			$this->postTypeClassName = 'Theme\Models\\' . Str::replace( '/', '\\', $this->postType );
			$namespace->addUse( $this->postTypeClassName );
		}

		return $class;
	}

	private function createPostTypesProperty( $class ) {
		$class->addProperty( 'postTypes', $this->postTypes )
			->setProtected()
			->setStatic()
			->addComment( 'Post type related to this taxonomy' )
			->addComment( "Same as for `register_taxonomy()`\n" )
			->addComment( '@var array|string' );
	}

	private function createFieldsMethodContent() {
		return "\$this->createFields(
	'container_id',
	esc_html__( 'Container label', 'brocooly' ),
	[
		Meta::text( 'example_meta', esc_html__( 'Example meta', 'brocooly' ) ),
	],
);";
	}

	private function createFieldsMethod( $class ) {
		$fieldsMethod = $this->createMethod( $class, 'fields', $this->createFieldsMethodContent() );
		$fieldsMethod
			->setProtected()
			->addComment( "Taxonomy metaboxes\n" )
			->addComment( '@return void' )
			->setReturnType( 'void' );
	}

	private function createOptionsMethodContent() {
		$taxonomyLabel       = Str::headline( $this->className );
		$pluralTaxonomyLabel = Str::plural( $taxonomyLabel );

		return "return [
	'labels'            => [
		'name'              => esc_html__( '{$pluralTaxonomyLabel}', 'brocooly' ),
		'all_items'         => esc_html__( 'All {$pluralTaxonomyLabel}', 'brocooly' ),
		'singular_name'     => esc_html__( '{$taxonomyLabel}', 'brocooly' ),
		'menu_name'         => esc_html__( '{$taxonomyLabel}', 'brocooly' ),
		'parent_item'       => esc_html__( 'Parent {$taxonomyLabel}', 'brocooly' ),
		'parent_item_colon' => esc_html__( 'Parent {$taxonomyLabel}', 'brocooly' ),
		'search_items'      => esc_html__( 'Find {$taxonomyLabel}', 'brocooly' ),
		'add_new_item'      => esc_html__( 'Add {$taxonomyLabel}', 'brocooly' ),
		'add_new'           => esc_html__( 'Add new {$taxonomyLabel}', 'brocooly' ),
	],
	'public'            => true,
	'show_in_menu'      => true,
	'show_ui'           => true,
	'hierarchical'      => true, // false for tags.
	'show_in_rest'      => false,
	'show_admin_column' => true,
	'rewrite'           => [
		'slug' => \$this->webUrl,
	],
	// 'meta_box'       => 'radio', // Use radio buttons in the meta box for this taxonomy on the post editing screen.
	// 'admin_cols'     => [],
];";
	}

	private function createOptionsMethod( $class ) {
		$optionsMethod = $this->createMethod( $class, 'options', $this->createOptionsMethodContent() );

		$optionsMethod
			->setProtected()
			->addComment( 'Taxonomy register options' )
			->addComment( "Same as for `register_extended_taxonomy()`\n" )
			->addComment( '@return array' )
			->setReturnType( 'array' );
	}

	private function createTaxonomyConstant( $class ) {
		$taxonomyConstant = $class->addConstant( 'TAXONOMY', $this->snakeCaseClassName );
		$taxonomyConstant->addComment( "Taxonomy slug\n" )
						->addComment( '@var string' );
	}

	private function createWebUrlProperty( $class ) {
		$class->addProperty( 'webUrl', $this->snakeCaseClassName )
				->setType( 'string' )
				->setPublic()
				->addComment( 'Web URL' )
				->addComment( "Publicly accessible name\n" )
				->addComment( '@var string' );
	}


}
