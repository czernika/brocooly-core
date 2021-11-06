<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Customizer\AbstractOption;
use Brocooly\Customizer\WPSection;
use Brocooly\Support\Facades\Mod;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeCustomizerOption extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:customizer:option';

	protected $fileNamespace = 'Theme\Customizer\Options';

	protected $themeFileFolder = 'Customizer/Options';

	protected function configure(): void
    {
        $this
			->addArgument(
				'option',
				InputArgument::REQUIRED,
				'Customizer option name',
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
		$name = $input->getArgument( 'option' );

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
		$file->addComment( $this->className . ' - custom customizer option' )
			->addComment( "! Register this class inside `customizer.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( AbstractOption::class )
					->addUse( Mod::class )
					->addUse( WPSection::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractOption::class );

		$optionName  = Str::snake( $this->className );
		$optionLabel = Str::headline( $this->className );
		$method = $this->createMethod(
			$class,
			'settings',
"return Mod::text(
	'{$optionName}',
	[
		// 'section'  => WPSection::TITLE_TAGLINE,
		'label'    => esc_html__( '{$optionLabel}', 'brocooly' ),
	],
);"
		);

		$method
			->addComment( "Create option instance\n" )
			->addComment( 'This will create simple text option' )
			->addComment( 'You need to specify WordPress section id' )
			->addComment( "For `Site Title & Tagline` it is `title_tagline`\n" )
			->addComment( '@link https://kirki.org/docs/controls/' )
			->addComment( '@link https://developer.wordpress.org/themes/customize-api/customizer-objects/#sections' )
			->addComment( '@return array' )
			->setReturnType( 'array' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Customizer option ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
