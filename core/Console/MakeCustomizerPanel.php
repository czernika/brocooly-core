<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Customizer\AbstractPanel;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeCustomizerPanel extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:customizer:panel';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Customizer\Panels';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Customizer/Panels';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this->addArgument(
				'panel',
				InputArgument::REQUIRED,
				'Customizer panel name',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'panel' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . ' - custom customizer panel',
			"! Register this class inside `config/customizer.php` file to have effect\n",
			'@see https://kirki.org/docs/setup/panels-sections/',
		]);

		$class = $this->generateClassCap();

		$this->createPanelIdConstant( $class );
		$this->createOptionsMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Customizer panel ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createOptionsMethod( $class ) {
		$panelName = Str::headline( $this->className );
		$method = $this->createMethod(
			$class,
			'options',
"return esc_html__( '{$panelName}', 'brocooly' );"
		);

		$method
			->addComment( "Panel settings\n" )
			->addComment( 'Create panel for customizer sections' )
			->addComment( "Same array as arguments for `Kirki::add_panel()` or string if only title required\n" )
			->addComment( '@return array|string' )
			->setReturnType( 'array|string' );
	}

	private function createPanelIdConstant( $class ) {
		$constant = $class->addConstant( 'PANEL_ID', $this->snakeCaseClassName );
		$constant->addComment( 'Panel id' )
						->addComment( "Same as `id` setting for `Kirki::add_panel()\n" )
						->addComment( "@var string" );
	}

	/**
	 * @return object
	 */
	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractPanel::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractPanel::class );

		return $class;
	}

}
