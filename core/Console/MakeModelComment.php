<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Models\Comment;
use Brocooly\Models\Users\User;
use Brocooly\Support\Facades\Meta;
use Brocooly\Support\Traits\HasAvatar;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeModelComment extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:comment';

	protected $fileNamespace = 'Theme\Models\WP';

	protected $themeFileFolder = 'Models/WP';

	/**
	 * Execute method
	 *
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{

		$io = new SymfonyStyle( $input, $output );

		$file = new \Nette\PhpGenerator\PhpFile();

		// Collect data
		$this->className  = 'Comment';
		$this->folderPath = '';

		// Create file content
		$file->addComment( "Base comment model for all roles\n" )
			->addComment( "! Register this class inside `Brocooly.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace );
		$namespace->addUse( Comment::class, 'BaseComment' );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( Comment::class );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'Comment base model was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
