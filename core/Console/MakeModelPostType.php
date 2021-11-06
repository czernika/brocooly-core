<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Customizer\AbstractSection;
use Brocooly\Models\PostType;
use Brocooly\Support\Facades\Meta;
use Brocooly\Support\Facades\Mod;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeModelPostType extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:post_type';

	protected $fileNamespace = 'Theme\Models';

	protected $themeFileFolder = 'Models';

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
	 * Execute method
	 *
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{

		$io = new SymfonyStyle( $input, $output );

		// Argument
		$name  = $input->getArgument( 'post_type' );
		$meta  = $input->getOption( 'meta' );

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
		$file->addComment( $this->className . ' - custom post type' )
			->addComment( "! Register this class inside `app.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( Meta::class )
					->addUse( PostType::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( PostType::class );

		$postTypeConstant = $class->addConstant( 'POST_TYPE', Str::snake( $this->className ) );
		$postTypeConstant->addComment( "Post type slug\n" )
						->addComment( '@var string' );

		$postTypeLabel       = Str::headline( $this->className );
		$pluralPostTypeLabel = Str::plural( $postTypeLabel );

		// protected options()
$optionsContent = "return [
	'labels'              => [
		'name'          => esc_html__( '{$pluralPostTypeLabel}', 'brocooly' ),
		'singular_name' => esc_html__( '{$postTypeLabel}', 'brocooly' ),
		'menu_name'     => esc_html__( '{$pluralPostTypeLabel}', 'brocooly' ),
		'add_new'       => esc_html__( 'Add New {$postTypeLabel}', 'brocooly' ),
	],
	'public'              => true,
	'exclude_from_search' => false,
	'show_in_rest'        => true,
	'menu_icon'           => null,
	'menu_position'       => 10,
	'supports'            => [ 'title', 'editor' ],
];";

		$optionsMethod = $this->createMethod( $class, 'options', $optionsContent );

		$optionsMethod
			->setProtected()
			->addComment( 'Post type register options' )
			->addComment( "Same as for `register_post_type()`\n" )
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
			->addComment( "Post type metaboxes\n" )
			->addComment( '@return void' )
			->setReturnType( 'void' );

	}

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Custom post type ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
