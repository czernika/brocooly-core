<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Support\Facades\Mod;
use Brocooly\Customizer\WPSection;
use Brocooly\Customizer\AbstractOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeCustomizerOption extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:customizer:option';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Customizer\Options';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Customizer/Options';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this->addArgument(
				'option',
				InputArgument::REQUIRED,
				'Customizer option name',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'option' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . ' - custom customizer option',
			"! Register this class inside `config/customizer.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		$this->createSettingsMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Customizer option ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	/**
	 * @return object
	 */
	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractOption::class )
					->addUse( Mod::class )
					->addUse( WPSection::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractOption::class );

		return $class;
	}

	private function createSettingsMethod( $class ) {
		$optionLabel = Str::headline( $this->className );
		$method = $this->createMethod(
			$class,
			'settings',
"return Mod::text(
	'{$this->snakeCaseClassName}',
	[
		// 'section' => WPSection::TITLE_TAGLINE,
		'label'   => esc_html__( '{$optionLabel}', 'brocooly' ),
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
	}

}
