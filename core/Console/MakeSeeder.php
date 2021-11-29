<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Router\View;
use Brocooly\Support\DB\Seeder;
use Brocooly\Support\Facades\File;
use Brocooly\UI\Menus\AbstractMenu;
use Brocooly\UI\Shortcodes\AbstractShortcode;
use Illuminate\Support\Str;
use Nette\PhpGenerator\Literal;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeSeeder extends CreateClassCommand
{

	protected $root = APP_PATH;

	protected $themeRootFolder = '/';

	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:seeder';

	protected $fileNamespace = 'Databases\Seeders';

	protected $themeFileFolder = 'databases/Seeders';

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
	 * Execute method
	 *
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{

		$io = new SymfonyStyle( $input, $output );

		// Argument
		$name = $input->getArgument( 'seeder' );

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
		$file->addComment( $this->className . " - database seeder\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( Seeder::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( Seeder::class );

		$postTypeSlug = Str::of( $postType )->after( '/' ) . '::class';
		$postTypeLiteral = new Literal( $postTypeSlug );

		$className = 'Theme\\Models\\' . Str::replace( '/', '\\', $postType );

		$namespace->addUse( $className );

		$postTypesProperty = $class->addProperty( 'seeder', $postTypeLiteral )
					->addComment( "Seeder post type\n" )
					->addComment( '@var object' );

		$timesProperty = $class->addProperty( 'times', $postType )
						->addComment( "How many times run seeder\n" )
						->setValue( 1 )
						->addComment( '@var int' );

		$method = $this->createMethod(
			$class,
			'params',
"return [
	'post_title'   => \$this->faker->name,
	'post_author'  => 1,
	'post_content' => \$this->faker->paragraph,,
];"
		);

		$method->addComment( "Return params as for `wp_insert_post`\n" )
						->addComment( '@return array' )
						->setReturnType( 'array' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Seeder ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
