<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Brocooly\Support\Facades\Meta;
use Brocooly\UI\Blocks\AbstractBlock;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeGutenberg extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:ui:block';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\UI\Blocks';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'UI/Blocks';

	/**
	 * @inheritDoc
	 */
	protected function configure(): void
    {
        $this->addArgument(
				'block',
				InputArgument::REQUIRED,
				'Gutenberg block name',
			);
    }

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$name = $input->getArgument( 'block' );

		$this->defineDataByArgument( $name );

		$this->generateClassComments([
			$this->className . " - custom theme block\n",
			"! Register this class inside `config/blocks.php` file to have effect\n",
		]);

		$class = $this->generateClassCap();

		$this->createTitleMethod( $class );
		$this->createFieldsMethod( $class );
		$this->createViewMethod( $class );

		$this->createFile( $this->file );

		$io->success( 'Gutenberg block ' . $name . ' was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	private function createFieldsMethod( $class ) {
		$filedsMethod = $this->createMethod(
			$class,
			'fields',
"return [
	Meta::text( 'example_text', esc_html__( 'Example text', 'brocooly' ) ),
];"
		);

		$filedsMethod
			->addComment( "Block fields\n" )
			->addComment( '@return array' )
			->setProtected()
			->setReturnType( 'array' );
	}

	private function createViewMethod( $class ) {
		$viewMethod = $this->createMethod(
			$class,
			'view',
"return 'path/to/view.twig';"
		);

		$viewMethod
			->addComment( "Block view file\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );
	}

	private function createTitleMethod( $class ) {
		$blockName = Str::headline( $this->className );
		$titleMethod = $this->createMethod(
			$class,
			'title',
"return esc_html__( '{$blockName} Gutenberg block', 'brocooly' );"
		);

		$titleMethod
			->addComment( "Block title\n" )
			->addComment( '@return string' )
			->setProtected()
			->setReturnType( 'string' );
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( AbstractBlock::class );
		$namespace->addUse( Meta::class );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( AbstractBlock::class );

		return $class;
	}

}
