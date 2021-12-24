<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Theme\Models\WP\Post;
use Illuminate\Support\Str;
use Nette\PhpGenerator\Literal;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeTemplate extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:template';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\UI\Templates';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'UI/Templates';

	/**
	 * Post types attached to template
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
				'template',
				InputArgument::REQUIRED,
				'Template name',
			)
			->addOption(
				'post_type',
				'p',
				InputOption::VALUE_OPTIONAL,
				'Link to post type',
			);
    }


	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name           = $input->getArgument( 'template' );
		$this->postType = $input->getOption( 'post_type' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme template\n",
			"! Register this class inside `config/views.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		if ( $this->postTypeClassName && ! class_exists( $this->postTypeClassName ) ) {
			$io->warning( 'Model class ' . $this->postTypeClassName . ' doesn\'t exists' );
		}

		$this->createSlugConstant( $class );
		$this->createPostTypesProperty( $class );
		$this->createLabelMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Template ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createSlugConstant( $class ) {
		$slugConstant = $class->addConstant( 'SLUG', $this->snakeCaseClassName );
		$slugConstant->addComment( "Template slug\n" )
						->addComment( '@var string' );
	}

	private function createPostTypesProperty( $class ) {
		$class->addProperty( 'postTypes', $this->postTypes )
						->setType( 'array' )
						->addComment( "Template post types\n" )
						->addComment( '@var array' );
	}

	private function createLabelMethod( $class ) {
		$templateLabel = Str::headline( $this->className );
$content = "return esc_html__( 'Template: {$templateLabel}', 'brocooly' );";
		$this->createMethod( $class, 'label', $content );
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file?->addNamespace( $this->rootNamespace );
		$class     = $namespace->addClass( $this->className );

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

}
