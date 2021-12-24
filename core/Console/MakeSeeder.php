<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Nette\PhpGenerator\Literal;
use Brocooly\Support\DB\Seeder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeSeeder extends CreateClassCommand
{

	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:seeder';

	/**
	 * @inheritDoc
	 */
	protected string $root = APP_PATH;

	/**
	 * @inheritDoc
	 */
	protected string $themeRootFolder = '/';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Databases\Seeders';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = '/databases/Seeders';

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
        $this
			->addArgument(
				'seeder',
				InputArgument::REQUIRED,
				'Seeder name',
			)
			->addOption(
				'post_type',
				null,
				InputOption::VALUE_REQUIRED,
				'Post type class associated with seeder',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name           = $input->getArgument( 'seeder' );
		$this->postType = $input->getOption( 'post_type' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - database seeder\n",
		]);

		$this->postTypeClassName = 'Theme\\Models\\' . Str::replace( '/', '\\', $this->postType );
		$class = $this->generateClassCap();


		if ( ! class_exists( $this->postTypeClassName ) ) {
			$io->warning( 'Model class ' . $this->postTypeClassName . ' doesn\'t exists' );
		}

		$this->createSeederProperty( $class );
		$this->createTimesProperty( $class );
		$this->createParamsMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Seeder ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createSeederProperty( $class ) {
		$postTypeSlug    = Str::of( $this->postType )->after( '/' ) . '::class';
		$postTypeLiteral = new Literal( $postTypeSlug );

		$class->addProperty( 'seeder', $postTypeLiteral )
					->addComment( "Seeder post type\n" )
					->addComment( '@var object' );
	}

	private function createTimesProperty( $class ) {
		$class->addProperty( 'times', $this->postType )
					->addComment( "How many times run seeder\n" )
					->setValue( 1 )
					->addComment( '@var int' );
	}

	private function createParamsMethod( $class ) {
		$method = $this->createMethod(
			$class,
			'params',
"return [
	'post_title'   => \$this->faker->name,
	'post_author'  => 1,
	'post_content' => \$this->faker->paragraph,
];"
		);

		$method->addComment( "Return params as for `wp_insert_post`\n" )
						->addComment( '@return array' )
						->setReturnType( 'array' );
	}

	/**
	 * @return object
	 */
	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( Seeder::class );
		$namespace->addUse( $this->postTypeClassName );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( Seeder::class );

		return $class;
	}

}
