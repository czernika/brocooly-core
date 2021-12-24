<?php
/**
 * Create custom theme sidebar class
 *
 * @package brocooly-core
 */

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\UI\Widgets\AbstractSidebar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeSidebar extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:sidebar';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\UI\Widgets\Sidebars';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'UI/Widgets/Sidebars';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this->addArgument(
				'sidebar',
				InputArgument::REQUIRED,
				'Sidebar location',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'sidebar' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme sidebar location\n",
			"! Register this class inside `config/widgets.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		$this->createSidebarIdConstant( $class );
		$this->createOptionsMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Sidebar location ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file?->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractSidebar::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractSidebar::class );

		return $class;
	}

	private function createSidebarIdConstant( $class ) {
		$constant = $class->addConstant( 'SIDEBAR_ID', $this->snakeCaseClassName );
		$constant->addComment( "Sidebar location\n" )
						->addComment( "@var string" );
	}

	private function createOptionsMethod( $class ) {
		$method = $this->createMethod(
			$class,
			'options',
			$this->createOptionsMethodContent(),
		);
		$method
			->addComment( "Get sidebar options\n" )
			->addComment( '@return array' )
			->setReturnType( 'array' );
	}

	private function createOptionsMethodContent() {
		$sidebarName = Str::headline( $this->className );
		return "return [
	'name'        => esc_html__( '{$sidebarName} location', 'brocooly' ),
	'description' => esc_html__( '{$sidebarName} description', 'brocooly' ),
];";
	}

}
