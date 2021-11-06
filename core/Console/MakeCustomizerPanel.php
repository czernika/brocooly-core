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

	protected $fileNamespace = 'Theme\Customizer\Panels';

	protected $themeFileFolder = 'Customizer/Panels';

	protected function configure(): void
    {
        $this
			->addArgument(
				'panel',
				InputArgument::REQUIRED,
				'Customizer panel name',
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
		$name = $input->getArgument( 'panel' );

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
		$file->addComment( $this->className . ' - custom customizer panel' )
			->addComment( "! Register this class inside `customizer.php` file\n" )
			->addComment( '@see https://kirki.org/docs/setup/panels-sections/' )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addUse( AbstractPanel::class );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractPanel::class );

		$panelConstant = $class->addConstant( 'PANEL_ID', Str::snake( $this->className ) );
		$panelConstant->addComment( 'Panel id' )
						->addComment( "Same as `id` setting for `Kirki::add_panel()\n" )
						->addComment( "@var string" );

		$method = $this->createMethod(
			$class,
			'options',
"return esc_html__( '{$this->className}', 'brocooly' );"
		);

		$method
			->addComment( "Panel settings\n" )
			->addComment( 'Create panel for customizer sections' )
			->addComment( "Same array as arguments for `Kirki::add_panel()` or string if only title required\n" )
			->addComment( '@return array|string' )
			->setReturnType( 'array|string' );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Customizer panel ' . $name . ' was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
