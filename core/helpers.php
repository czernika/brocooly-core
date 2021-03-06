<?php
/**
 * Framework core helper functions and definitions
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

use Brocooly\App;
use Timber\Timber;
use Brocooly\Router\View;
use Brocooly\Storage\Config;
use Webmozart\Assert\Assert;
use Brocooly\Loaders\AssetsLoader;
use Brocooly\Http\Request\Request;

use function Env\env;

/**
 * --------------------------------------------------------------------------
 * Define core root path
 * --------------------------------------------------------------------------
 */
if ( ! defined( 'BROCOOLY_CORE_PATH' ) ) {
	define( 'BROCOOLY_CORE_PATH', __DIR__ );
}

/**
 * @deprecated 0.16.1 Same as `BROCOOLY_CORE_PATH`
 */
if ( ! defined( 'CORE_PATH' ) ) {
	define( 'CORE_PATH', __DIR__ );
}

/**
 * Version of core framework itself
 */
if ( ! defined( 'BROCOOLY_CORE_VERSION' ) ) {
	define( 'BROCOOLY_CORE_VERSION', '1.0.0' );
}

/**
 * --------------------------------------------------------------------------
 * Helper functions
 * --------------------------------------------------------------------------
 * NOTE: we do NOT prefix them
 * One day we will as it is not independent Framework after all
 */
if ( ! function_exists( 'isTimberNext' ) ) {

	/**
	 * Compare Timber version is it later than 2.0
	 *
	 * @return bool
	 */
	function isTimberNext() {
		return version_compare( Timber::$version, '2', '>=' );
	}
}

if ( ! function_exists( 'isCurrentEnv' ) ) {

	/**
	 * Define if application is running in $env mode
	 *
	 * @param string $env | environment.
	 * @return bool
	 */
	function isCurrentEnv( string $env ) {
		return env( 'WP_ENV' ) === $env;
	}
}

if ( ! function_exists( 'isProduction' ) ) {

	/**
	 * Define if application is running in production mode
	 *
	 * @return bool
	 */
	function isProduction() {
		return isCurrentEnv( 'production' );
	}
}

if ( ! function_exists( 'container' ) ) {

	/**
	 * Return container instance
	 *
	 * @return \DI\Container
	 */
	function container() {
		return once(
			function() {
				return require_once CORE_PATH . '/container.php';
			}
		);
	}
}

if ( ! function_exists( 'app' ) ) {

	/**
	 * Return application instance
	 *
	 * @param string|null $key | key to get.
	 */
	function app( $key = null ) {
		$app = App::instance();
		if ( isset( $key ) ) {
			return $app->get( $key );
		}
		return $app;
	}
}

if ( ! function_exists( 'config' ) ) {

	/**
	 * Return configuration object or provided default value
	 *
	 * @param string $key | key value.
	 * @param mixed  $default | default value if key was set and not found.
	 * @return mixed
	 */
	function config( string $key = null, $default = null ) {
		if ( Config::get( $key ) || null === $key ) {
			return Config::get( $key );
		}

		return $default;
	}
}

if ( ! function_exists( 'dump' ) ) {

	/**
	 * Dump helper
	 *
	 * @param mixed $val | value to check.
	 */
	function dump( $val ) {
		echo '<pre>';
		print_r( $val );
		echo '</pre>';
	}
}

if ( ! function_exists( 'dd' ) ) {

	/**
	 * Dump and die helper
	 *
	 * @param mixed $val | value to check.
	 */
	function dd( $val ) {
		dump( $val );
		die();
	}
}

if ( ! function_exists( 'asset' ) ) {

	/**
	 * Get asset path from manifest file
	 *
	 * @param string $filePath | value to check.
	 * @return string
	 */
	function asset( string $filePath ) {
		$asset = app( AssetsLoader::class )->asset( $filePath );

		if ( $asset ) {
			$public         = trailingslashit( config( 'assets.public' ) );
			$publicFilePath = BROCOOLY_THEME_URI . $public . $asset;
			return $publicFilePath;
		}

		return BROCOOLY_THEME_URI . 'resources/' . $filePath;
	}
}

if ( ! function_exists( 'view' ) ) {

	/**
	 * Render helper
	 *
	 * @param string $views | view file to be rendered.
	 * @param array  $ctx | context to pass with.
	 * @return string
	 */
	function view( string $views, array $ctx = [] ) {
		return View::make( $views, $ctx );
	}
}

if ( ! function_exists( 'mod' ) ) {

	/**
	 * Theme mod wrapper
	 *
	 * @param string  $key | theme mod helper.
	 * @param mixed   $default | default value.
	 * @param boolean $prefixed | prefix value or not.
	 * @return mixed
	 */
	function mod( string $key, $default = null, bool $prefixed = true ) {
		$themeMod = $key;

		if ( $prefixed ) {
			$prefix   = config( 'customizer.prefix' );
			$themeMod = $prefix . $key;
		}
		return get_theme_mod( $themeMod, $default );
	}
}

if ( ! function_exists( 'request' ) ) {

	/**
	 * Get WordPress request object
	 *
	 * @param string|null $key | key from request.
	 * @return mixed
	 */
	function request( $key = null ) {
		$request = app( Request::class );
		if ( isset( $key ) ) {
			return $request->get( $key );
		}

		return $request;
	}
}

if ( ! function_exists( 'action' ) ) {

	/**
	 * Set action form handler
	 *
	 * @param string|null $name | action name.
	 * @return callable
	 */
	function action( string $name ) {
		echo esc_url( site_url() ) . '/wp-admin/admin-post.php?action=' . $name;
	}
}

if ( ! function_exists( 'task' ) ) {

	/**
	 * Call task
	 *
	 * @param string $task | task class.
	 * @return void
	 */
	function task( string $task, array $args = [] ) {
		$taskObject = app( $task );

		Assert::methodExists( $taskObject, 'run', 'You should have `run()` method inside your task' );

		return $taskObject->run( ...$args );
	}
}

if ( ! function_exists( 'sprite' ) ) {

	/**
	 * Return SVG sprite path
	 *
	 * @param string $svgId | SVG icon ID attribute
	 * @return string
	 */
	function sprite( string $svgId ) {
		$spriteFileName = 'spritemap.svg';
		$spriteFileUri  = BROCOOLY_THEME_URI . 'public/' . $spriteFileName;
		return $spriteFileUri . '#' . $svgId;
	}
}

if ( ! function_exists( 'session' ) ) {

	/**
	 * Retrieve Session object
	 *
	 * @return object
	 */
	function session() {
		return app( 'session' );
	}
}

if ( ! function_exists( 'flash' ) ) {

	/**
	 * Retrieve Session flash object
	 *
	 * @return mixed
	 */
	function flash( $key = null ) {
		$flashBag = session()->getFlashBag();

		if ( $key ) {
			return $flashBag->get( $key );
		}
 
		return $flashBag;
	}
}
