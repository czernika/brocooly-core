<?php
/**
 * Create custom theme shortcode class
 *
 * @package brocooly-core
 */

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Router\View;
use Brocooly\UI\Shortcodes\AbstractShortcode;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeShortcode extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:shortcode';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\UI\Shortcodes';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'UI/Shortcodes';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this->addArgument(
				'shortcode',
				InputArgument::REQUIRED,
				'Shortcode name',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'shortcode' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme shortcode\n",
			"! Register this class inside `config/views.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		$this->createShortcodeIdConstant( $class );

		$this->createRenderMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Shortcode ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractShortcode::class );
		$namespace->addUse( View::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractShortcode::class );

		return $class;
	}

	private function createShortcodeIdConstant( $class ) {
		$constant = $class->addConstant( 'SHORTCODE_ID', $this->snakeCaseClassName );
		$constant->addComment( "Shortcode tag to be searched in post content\n" )
						->addComment( "@var string" );
	}

	private function createRenderMethod( $class ) {
		$method = $this->createMethod(
			$class,
			'render',
			$this->createRenderMethodContent(),
		);

		$method
			->addComment( "Render shortcode\n" )
			->addComment( 'The callback function to run when the shortcode is found.' )
			->addComment( "! Function called by the shortcode should never produce output of any kind.\n" )
			->addComment( '@var array $atts | shortocde attributes.' )
			->addComment( '@example available on front as:' )
			->addComment( '```' )
			->addComment( '{% apply shortcodes %}' )
			->addComment( "[{$this->snakeCaseClassName} example=\"value\"]" )
			->addComment( '{% endapply %}' )
			->addComment( '```' );

		$method->addParameter( 'atts', [] )
				->setType( 'array' );
	}

	private function createRenderMethodContent() {
		$viewFile = 'path/to/view.twig';
		return "\$example = false;
if ( isset( \$atts['example'] ) ) {
	\$example = sanitize_text_field( \$atts['example'] );
}

// ! shortcode HAVE TO return something
return View::compile( '{$viewFile}', compact( 'example' ) );";
	}

}
