<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Support\Facades\Meta;
use Brocooly\UI\Widgets\AbstractWidget;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeWidget extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:widget';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\UI\Widgets';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'UI/Widgets';

	/**
	 * Human-readable Widget name
	 *
	 * @var string
	 */
	private string $widgetName = 'Custom Widget';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this
			->addArgument(
				'widget',
				InputArgument::REQUIRED,
				'Widget name',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'widget' );

		$this->defineDataByArgument( $name );
		$this->widgetName = Str::headline( $this->className );

		$this->generateClassComments([
			$this->className . " - custom theme widget\n",
			"! Register this class inside `config/widgets.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		$this->createWidgetIdConstant( $class );
		$this->createTitleMethod( $class );
		$this->createDescriptionMethod( $class );
		$this->createOptionsMethod( $class );
		$this->createViewMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Widget ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createViewMethod( $class ) {
		$viewMethod = $this->createMethod(
			$class,
			'view',
"return 'path/to/view.twig';"
		);

		$viewMethod
			->addComment( "Widget view instance\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );
	}

	private function createOptionsMethod( $class ) {
		$optionsMethod = $this->createMethod(
			$class,
			'options',
"return [
	Meta::text( 'title', esc_html__( 'Title', 'brocooly' ) ),
];"
		);

		$optionsMethod
			->addComment( "Widget options\n" )
			->addComment( '@return array' )
			->setProtected()
			->setReturnType( 'array' );
	}

	private function createDescriptionMethod( $class ) {
		$descriptionMethod = $this->createMethod(
			$class,
			'description',
"return esc_html__( '{$this->widgetName} description', 'brocooly' );"
		);

		$descriptionMethod
			->addComment( "Widget description\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );
	}

	private function createTitleMethod( $class ) {
		$titleMethod = $this->createMethod(
			$class,
			'title',
"return esc_html__( 'Brocooly | {$this->widgetName}', 'brocooly' );"
		);

		$titleMethod
			->addComment( "Widget title\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );
	}

	private function createWidgetIdConstant( $class ) {
		$panelConstant = $class->addConstant( 'WIDGET_ID', $this->snakeCaseClassName );
		$panelConstant->addComment( "Widget id\n" )
						->addComment( "@var string" );
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractWidget::class );
		$namespace->addUse( Meta::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractWidget::class );

		return $class;
	}

}
