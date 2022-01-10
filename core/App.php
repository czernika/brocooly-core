<?php
/**
 * Main application instance class
 * All theme logic starts here
 *
 * @package Brocooly-core
 * @since 0.1.0
 */

declare(strict_types=1);

namespace Brocooly;

use Timber\Timber;
use Brocooly\Router\Router;
use Brocooly\Loaders\HookLoader;
use Brocooly\Loaders\AssetsLoader;
use Brocooly\Support\Facades\File;
use Brocooly\Loaders\BootProvider;
use Brocooly\Loaders\ConfigLoader;
use Brocooly\Router\RequestHandler;
use Brocooly\Loaders\DebuggerLoader;
use Psr\Container\ContainerInterface;
use Brocooly\Loaders\DefinitionLoader;
use Brocooly\Loaders\RegisterProvider;
use Brocooly\Support\Traits\AppContainer;
use Brocooly\Support\Traits\HasContainer;
use Brocooly\Contracts\AppContainerInterface;

class App implements AppContainerInterface
{

	use HasContainer, AppContainer;

	/**
	 * Timber instance
	 *
	 * @var object
	 */
	public Timber $timber;

	/**
	 * DI Container
	 *
	 * @var object
	 */
	private $container;

	/**
	 * Router
	 *
	 * @var object
	 */
	private $router;

	/**
	 * Check if is app was already booted
	 *
	 * @var bool
	 */
	private bool $booted = false;

	/**
	 * App itself
	 *
	 * @var object
	 */
	private static $app;

	/**
	 * Array of Application loaders
	 * ! Order matter - consider to load in a first place "important" loaders
	 *
	 * @var array
	 */
	private array $loaders = [
		DefinitionLoader::class,
		DebuggerLoader::class,
		ConfigLoader::class,
		RegisterProvider::class,
		HookLoader::class,
		AssetsLoader::class,
		BootProvider::class,
	];

	/**
	 * Define if route resolver was called
	 *
	 * @var bool
	 */
	private bool $webRoutesWasLoaded = false;

	public function __construct(
		ContainerInterface $container,
		Timber $timber,
		Router $router,
	)
	{
		$this->setContainer( $container );

		$this->container = $this->container();
		self::$app       = $this;
		$this->timber    = $timber;
		$this->router    = $router;
	}

	public static function instance()
	{
		return static::$app;
	}

	/**
	 * Resolve routes
	 * Include web.php file and resolve routes.
	 *
	 * @since 0.10.0
	 * @return void
	 */
	public function web()
	{
		if ( ! $this->webRoutesWasLoaded ) {
			$this->webRoutesWasLoaded = true;

			/**
			 * Include routes files
			 */
			File::requireOnce( BROCOOLY_THEME_PATH . '/routes/web.php' );

			/**
			 * Resolve routes
			 */
			$this->router->resolve();
		}
	}

	/**
	 * Handle AJAX and POST requests
	 *
	 * @return void
	 */
	public function ajax() {
		File::requireOnce( BROCOOLY_THEME_PATH . 'routes/ajax.php' );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			RequestHandler::handleAjaxRequest();
		}

		RequestHandler::handlePostRequest();
	}

	/**
	 * Boot loaders.
	 * Make instance of this loader
	 *
	 * @param array $loaders | array of app loaders.
	 */
	public function run()
	{
		if ( ! $this->booted ) {
			foreach ( $this->loaders as $loader ) {
				if ( method_exists( $loader, 'call' ) ) {
					$this->call( [ $loader, 'call' ] );
				}
			}
			$this->booted = true;
		}
	}
}
