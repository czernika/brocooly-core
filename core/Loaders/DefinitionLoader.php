<?php
/**
 * Load theme constants and definitions
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Loaders;

class DefinitionLoader
{

	/**
	 * Register theme definitions and constants
	 *
	 * @return void
	 */
	public function call() {
		if ( ! defined( 'BROCOOLY_THEME_PATH' ) ) {
			define( 'BROCOOLY_THEME_PATH', trailingslashit( get_template_directory() ) );
		}

		if ( ! defined( 'BROCOOLY_THEME_URI' ) ) {
			define( 'BROCOOLY_THEME_URI', trailingslashit( get_template_directory_uri() ) );
		}
	}
}
