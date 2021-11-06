<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Models\Users\User;
use Brocooly\Support\Facades\Meta;
use Brocooly\Support\Traits\HasAvatar;
use Illuminate\Support\Str;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeModelUser extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:user';

	protected $fileNamespace = 'Theme\Models\Users';

	protected $themeFileFolder = 'Models/Users';

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
		$this->className  = 'User';
		$this->folderPath = '';

		// Create file content
		$file->addComment( "Base user model for all roles\n" )
			->addComment( "! Register this class inside `Brocooly.php` file\n" )
			->addComment( '@package Brocooly' )
			->setStrictTypes();

		$namespace = $file->addNamespace( $this->fileNamespace );
		$namespace->addUse( Meta::class );
		$namespace->addUse( HasAvatar::class );
		$namespace->addUse( User::class, 'BaseUser' );

		$class = $namespace->addClass( $this->className );
		$class->addExtend( User::class )
				->addTrait( HasAvatar::class );

		// Create file
		$this->createFile( $file );

		// Output
		$io->success( 'User base model was successfully created' );

		return CreateClassCommand::SUCCESS;
	}

}
