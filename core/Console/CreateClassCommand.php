<?php

declare(strict_types=1);

namespace Brocooly\Console;

use Brocooly\Support\Facades\File;
use Symfony\Component\Console\Command\Command;

class CreateClassCommand extends Command
{

	protected $root = BROCOOLY_THEME_PATH;

	protected $themeRootFolder = '/src/';

	protected $fileNamespace = 'Theme';

	protected $themeFileFolder = '';

	protected $folderPath = '';

	protected $className = '';

	protected function createClass( $file, $blockName ) {

		$namespaces = explode( '/', $blockName );
		$origin = count( $namespaces );
		$className  = end( $namespaces );

		if ( $origin > 1 ) {
			unset( $namespaces[ $origin - 1 ]);
		}

		$classNamespace = $origin > 1 ?
							'\\' . implode( '\\', $namespaces ) :
							'';

		$namespace = $file->addNamespace( $this->fileNamespace . $classNamespace );
		$namespace->addClass( $className );

		$folderPath = $origin > 1 ?
						'/' . implode( '/', $namespaces ) :
						'';

		$folder = BROCOOLY_THEME_PATH . $this->themeRootFolder . $this->themeFileFolder . $folderPath;

		File::ensureDirectoryExists( $folder );

		$filename = $folder . '/' .  $className . '.php';

		File::put( $filename, $file );
	}

	protected function createMethod( $class, $name, $body = '//...' ) {
		$method = $class->addMethod( $name );
		$method->setBody( $body );
		return $method;
	}

	protected function createFile( $file ) {
		$folder = $this->root . $this->themeRootFolder . $this->themeFileFolder . $this->folderPath;
		File::ensureDirectoryExists( $folder );

		$filename = $folder . '/' .  $this->className . '.php';
		File::put( $filename, $file );
	}
}
