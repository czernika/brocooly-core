<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\UI\Menus\AbstractMenu;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class MakeMenu extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:menu';

	/**
	 * @inheritDoc
	 */
	protected string $fileNamespace = 'Theme\UI\Menus';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'UI/Menus';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this->addArgument(
				'menu',
				InputArgument::REQUIRED,
				'Menu location name',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'menu' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme menu location\n",
			"! Register this class inside `config/menus.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		$this->createLocationConstant( $class );
		$this->createLabelMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Menu ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createLabelMethod( $class ) {
		$menuName = Str::headline( $this->className );
		$method = $this->createMethod(
			$class,
			'label',
"return esc_html__( '{$menuName}', 'brocooly' );"
		);

		$method
			->addComment( "Get menu label in admin area\n" )
			->addComment( '@return string' )
			->setReturnType( 'string' );
	}

	private function createLocationConstant( $class ) {
		$constant = $class->addConstant( 'LOCATION', $this->snakeCaseClassName );
		$constant->addComment( "Menu location\n" )
						->addComment( "@var string" );
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractMenu::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractMenu::class );

		return $class;
	}

}
