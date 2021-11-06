<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Customizer\AbstractSection;
use Brocooly\Support\Facades\Mod;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeCustomizerSection extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:customizer:section';

	protected $fileNamespace = 'Theme\Customizer\Sections';

	protected $themeFileFolder = 'Customizer/Sections';

	protected function configure(): void
    {
        $this
			->addArgument(
				'section',
				InputArgument::REQUIRED,
				'Customizer section name',
			)
			->addOption(
				'panel',
				null,
				InputOption::VALUE_REQUIRED,
				'Set panel class as section\'s parent. Pass relative class name to Theme\\Customizer\\Panels	',
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
		$name  = $input->getArgument( 'section' );
		$panel = $input->getOption( 'panel' );

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
		$file->addComment( $this->className . ' - custom customizer section' )
			->addComment( "! Register this class inside `customizer.php` file\n" )
			->addComment( '@see https://kirki.org/docs/setup/panels-sections/' )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( Mod::class )
					->addUse( AbstractSection::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractSection::class );

		$sectionConstant = $class->addConstant( 'SECTION_ID', Str::snake( $this->className ) );
		$sectionConstant->addComment( 'Section id' )
						->addComment( "Same as `id` setting for `Kirki::add_section()\n" )
						->addComment( "@var string" );

		$sectionName = Str::headline( $this->className );
		if ( $panel ) {
			$panelNamespaces = explode( '/', $panel );
			$panelClassName = end( $panelNamespaces );

$optionsContent = "return [
	'title' => esc_html__( '{$sectionName}', 'brocooly' ),
	'panel' => {$panelClassName}::PANEL_ID,
];";

			$namespace->addUse( 'Theme\Customizer\Panels\\' . Str::replace( '/', '\\', $panel ) );

		} else {
			$optionsContent = "return esc_html__( '{$sectionName}', 'brocooly' );";
		}

		// Options()
		$optionsMethod = $this->createMethod( $class, 'options', $optionsContent );

		$optionsMethod
			->addComment( "Panel settings\n" )
			->addComment( 'Create panel for customizer sections' )
			->addComment( "Same array as arguments for `Kirki::add_panel()` or string if only title required\n" )
			->addComment( '@return array|string' )
			->setReturnType( 'array|string' );

$controlsContent = "return [
	// Mod::text( 'example_setting', ['label' => esc_html__( 'Example setting', 'brocooly' ) ]),
];";

		// Controls()
		$controlsMethod = $this->createMethod( $class, 'controls', $controlsContent );

		$controlsMethod
			->addComment( "Section controls\n" )
			->addComment( '@see https://kirki.org/docs/controls/' )
			->addComment( '@return array' )
			->setReturnType( 'array' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Customizer panel ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
