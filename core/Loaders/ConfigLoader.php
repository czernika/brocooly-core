<?php
/**
 * Boot Config instance
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Loaders;

use Brocooly\App;
use Brocooly\Storage\Config;

class ConfigLoader
{

	/**
	 * Application instance
	 *
	 * @var instanceof Brocooly\App
	 */
	private $app;

	public function __construct( App $app ) {
		$this->app = $app;
	}

	/**
	 * Get all configuration data from `config` folder
	 */
	public function call() {
		$configFiles = glob( wp_normalize_path( BROCOOLY_THEME_CONFIG_PATH . '/*.php' ) );

		$data = [];
		foreach ( $configFiles as $file ) {
			$data[ pathinfo( $file )['filename'] ] = require_once $file;
		}

		$this->app->get( Config::class )::$data = $data;
	}
}
