<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Models\Users\User;
use Brocooly\Support\Facades\Meta;
use Brocooly\Support\Traits\HasAvatar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModelUser extends CreateClassCommand
{
	/**
	 * The name of the command
	 *
	 * @var string
	 */
	protected static $defaultName = 'new:model:user';

	/**
	 * @inheritDoc
	 */
	protected string $rootNamespace = 'Theme\Models\Users';

	/**
	 * @inheritDoc
	 */
	protected string $themeFileFolder = 'Models/Users';

	/**
	 * @inheritDoc
	 */
	protected function execute( InputInterface $input, OutputInterface $output ) : int
	{
		$io = new SymfonyStyle( $input, $output );

		$this->className  = 'User';
		$this->folderPath = '';

		$class = $this->generateClassCap();

		$this->generateClassComments([
			"Base user model for all roles\n",
			"! Set this class as `users.parent` inside `Http/Brocooly.php` file\n"
		]);

		$this->createFile( $this->file );

		$io->success( 'User base model was successfully created' );
		return CreateClassCommand::SUCCESS;
	}

	protected function generateClassCap() {
		// Generate class namespace
		$namespace = $this->file->addNamespace( $this->rootNamespace );
		$namespace->addUse( Meta::class );
		$namespace->addUse( HasAvatar::class );
		$namespace->addUse( User::class, 'BaseUser' );

		// Generate extend class
		$class = $namespace->addClass( $this->className );
		$class->addExtend( User::class )
				->addTrait( HasAvatar::class );

		return $class;
	}

}
