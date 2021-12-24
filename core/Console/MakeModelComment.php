<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Models\Comment;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModelComment extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:comment';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Models\WP';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Models/WP';

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$this->className  = 'Comment';
		$this->folderPath = '';

		$class = $this->generateClassCap();

		$this->generateClassComments([
			"Base comment model\n",
			"! Set this class as `comments.parent` inside `Http/Brocooly.php` file\n"
		]);

		$this->createFile( $this->file );

		$io->success( 'Comment base model was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( Comment::class, 'BaseComment' );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( Comment::class );

		return $class;
	}

}
