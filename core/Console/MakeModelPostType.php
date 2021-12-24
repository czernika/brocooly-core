<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Models\PostType;
use Brocooly\Support\Facades\Meta;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModelPostType extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:post_type';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Models';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Models';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this
			->addArgument(
				'post_type',
				InputArgument::REQUIRED,
				'Create custom post type',
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

		$name = $input->getArgument( 'post_type' );
		$meta = $input->getOption( 'meta' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . ' - custom post type',
			"! Register this class inside `config/app.php` file to have effect\n",
			"! It is recommended to flush permalinks\n",
		]);

		$class = $this->generateClassCap();

		$this->createPostTypeConstant( $class );
		$this->createOptionsMethod( $class );
		$this->createWebUrlProperty( $class );

		if ( $meta ) {
			$this->createFieldsMethod( $class );
		}

		$this->createFile( $this->file );

		$io->success( 'Custom post type ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createWebUrlProperty( $class ) {
		$class->addProperty( 'webUrl', $this->snakeCaseClassName )
				->setType( 'string' )
				->setPublic()
				->addComment( 'Web URL' )
				->addComment( "Publicly accessible name\n" )
				->addComment( '@var string' );
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
			->addComment( "Post type metaboxes\n" )
			->addComment( '@return void' )
			->setReturnType( 'void' );
	}

	private function createOptionsMethodContent() {
		$postTypeLabel       = Str::headline( $this->className );
		$pluralPostTypeLabel = Str::plural( $postTypeLabel );

		return "return [
	'labels'              => [
		'name'           => esc_html__( '{$pluralPostTypeLabel}', 'brocooly' ),
		'all_items'      => esc_html__( '{$pluralPostTypeLabel}', 'brocooly' ),
		'singular_name'  => esc_html__( '{$postTypeLabel}', 'brocooly' ),
		'name_admin_bar' => esc_html__( '{$postTypeLabel}', 'brocooly' ),
		'menu_name'      => esc_html__( '{$pluralPostTypeLabel}', 'brocooly' ),
		'add_new'        => esc_html__( 'Add {$postTypeLabel}', 'brocooly' ),
		'new_item'       => esc_html__( 'New {$postTypeLabel}', 'brocooly' ),
		'add_new_item'   => esc_html__( 'Add new {$postTypeLabel}', 'brocooly' ),
		'search_items'   => esc_html__( 'Find {$postTypeLabel}', 'brocooly' ),
		'edit_item'      => esc_html__( 'Edit {$postTypeLabel}', 'brocooly' ),
		'view_item'      => esc_html__( 'View {$postTypeLabel}', 'brocooly' ),
	],
	'public'              => true,
	'exclude_from_search' => false,
	'show_in_rest'        => false,
	'menu_icon'           => null,
	'menu_position'       => 10,
	'supports'            => [ 'title', 'editor' ],
	'enter_title_here'    => esc_html__( 'New {$postTypeLabel} title', 'brocooly' ),
	// 'admin_cols'       => [],
	// 'admin_filters'    => [],
];";
	}

	private function createOptionsMethod( $class ) {
		$optionsMethod = $this->createMethod( $class, 'options', $this->createOptionsMethodContent() );

		$optionsMethod
			->setProtected()
			->addComment( 'Post type register options' )
			->addComment( "Same as for `register_extended_post_type()`\n" )
			->addComment( '@return array' )
			->setReturnType( 'array' );
	}

	private function createPostTypeConstant( $class ) {
		$postTypeConstant = $class->addConstant( 'POST_TYPE', $this->snakeCaseClassName );
		$postTypeConstant->addComment( "Post type slug\n" )
						->addComment( '@var string' );
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( PostType::class )
				->addUse( Meta::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( PostType::class );

		return $class;
	}

}
