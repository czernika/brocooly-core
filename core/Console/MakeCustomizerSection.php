<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Support\Facades\Mod;
use Brocooly\Customizer\AbstractSection;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCustomizerSection extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:customizer:section';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Customizer\Sections';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Customizer/Sections';

	/**
	 * Section name
	 *
	 * @var string
	 */
	private string $sectionName = '';

	/**
	 * Panel name
	 *
	 * @var string|null
	 */
	private ?string $panel = null;

	/**
	 * Panel name in human-readable format
	 *
	 * @var string|null
	 */
	private ?string $panelName = null;

	/**
	 * Panel namespace
	 *
	 * @var string|null
	 */
	private ?string $panelNamespace = null;

	/**
	 * @inheritDoc
	 */
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
				'Set panel class as section\'s parent. Pass relative class name to Theme\\Customizer\\Panels',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name        = $input->getArgument( 'section' );
		$this->panel = $input->getOption( 'panel' );

		$this->defineDataByArgument( $name );
		$this->sectionName = Str::headline( $this->className );

		if ( $this->panel ) {
			$this->panelName      = Str::afterLast( $this->panel, '/' );
			$this->panelNamespace = 'Theme\\Customizer\\Panels\\' . Str::replace( '/', '\\', $this->panel );
		}

		$this->generateClassComments([
			$this->className . ' - custom customizer section',
			"! Register this class inside `config/customizer.php` file to have effect\n",
			'@see https://kirki.org/docs/setup/panels-sections/',
		]);

		$class = $this->generateClassCap();

		$this->createSectionIdConstant( $class );
		$this->createOptionsMethod( $class );
		$this->createControlsMethod( $class );

		if ( $this->panel && ! class_exists( $this->panelNamespace ) ) {
			$io->warning( 'Model class ' . $this->panelNamespace . ' doesn\'t exists' );
		}

		$this->createFile( $this->file );

		$io->success( 'Customizer section ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createOptionsMethodContent() {
		if ( ! $this->panel ) {
			return "return esc_html__( '{$this->sectionName}', 'brocooly' );";
		}


		return "return [
	'title' => esc_html__( '{$this->sectionName}', 'brocooly' ),
	'panel' => {$this->panelName}::PANEL_ID,
];";
	}

	private function createOptionsMethod( $class ) {
		$optionsMethod = $this->createMethod( $class, 'options', $this->createOptionsMethodContent() );

		$optionsMethod
			->addComment( "Section settings\n" )
			->addComment( "Same array as arguments for `Kirki::add_panel()` or string if only title required\n" )
			->addComment( '@return array|string' )
			->setReturnType( 'array|string' );
	}

	private function createControlsMethodContent() {
		return "return [
	// Mod::text( 'example_setting', ['label' => esc_html__( 'Example setting', 'brocooly' ) ]),
];";
	}

	private function createControlsMethod( $class ) {
		$controlsMethod = $this->createMethod( $class, 'controls', $this->createControlsMethodContent() );

		$controlsMethod
			->addComment( "Section controls\n" )
			->addComment( '@see https://kirki.org/docs/controls/' )
			->addComment( '@return array' )
			->setReturnType( 'array' );
	}

	private function createSectionIdConstant( $class ) {
		$sectionConstant = $class->addConstant( 'SECTION_ID', $this->snakeCaseClassName );
		$sectionConstant->addComment( 'Section id' )
						->addComment( "Same as `id` setting for `Kirki::add_section()\n" )
						->addComment( "@var string" );
	}

	/**
	 * @return object
	 */
	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractSection::class )
					->addUse( Mod::class );

		if ( $this->panel ) {
			$namespace->addUse( 'Theme\\Customizer\\Panels\\' . Str::replace( '/', '\\', $this->panel ) );
		}

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractSection::class );

		return $class;
	}

}
