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
use Brocooly\Support\Facades\File;
use Brocooly\Loaders\HookLoader;
use Brocooly\Loaders\AssetsLoader;
use Brocooly\Loaders\BootProvider;
use Brocooly\Loaders\ConfigLoader;
use Brocooly\Loaders\DebuggerLoader;
use Brocooly\Loaders\DefinitionLoader;
use Brocooly\Loaders\RegisterProvider;
use Brocooly\Contracts\AppContainerInterface;
use Brocooly\Support\Traits\AppContainer;
use Brocooly\Support\Traits\HasContainer;
use Psr\Container\ContainerInterface;

class App implements AppContainerInterface
{

	use HasContainer, AppContainer;

	/**
	 * Timber instance
	 *
	 * @var instanceof Timber\Timber
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
	 * @var instanceof \Brocooly\Router\Router
	 */
	private $router;

	/**
	 * Check if is app was already booted
	 *
	 * @var bool
	 */
	private bool $booted = false;

	private static $app;

	/**
	 * Array of Application loaders
	 * ! Order matter - consider to load in a first place important loaders
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

	public function __construct( ContainerInterface $container )
	{

		$this->checkMinRequirements();

		$this->setContainer( $container );

		$this->container = $this->container();
		self::$app       = $this;
		$this->timber    = $this->container->get('timber');
		$this->router    = $this->container->get('routing');
	}

	private function checkMinRequirements() {

		/**
		 * --------------------------------------------------------------------------
		 * Ensure compatible version of PHP is used
		 * --------------------------------------------------------------------------
		 *
		 * Minimum required version is 7.4.
		 */
		$brocooly_min_php_version = '7.4';
		if ( version_compare( $brocooly_min_php_version, phpversion(), '>=' ) ) {
			wp_die(
				/* translators: 1 - minimum required PHP version, 2 - current PHP version. */
				sprintf(
					/* html */
					'<h1>Brocooly Framework requires PHP version %1$s or greater!</h1><p>Invalid PHP version! Please update it. Your current version is: <strong>%2$s</strong></p>',
					esc_html( $brocooly_min_php_version ),
					esc_html( phpversion() ),
				),
			);
		}


		/**
		 * --------------------------------------------------------------------------
		 * Check if Composer is installed
		 * --------------------------------------------------------------------------
		 *
		 * ! Brocooly STRONGLY requires Composer to be installed.
		 * If it's not go to and install.
		 *
		 * @link https://getcomposer.org/
		 */
		$autoload = APP_PATH . '/vendor/autoload.php';
		if ( ! file_exists( $autoload ) ) {
			wp_die(
				/* translators: 1 - root directory, 2 - link to Composer website. */
				sprintf(
					/* html */ '<h1>Forester Framework requires composer to be installed!</h1><p>Maybe you forget to run <code>composer update</code> in the root folder: <strong>%1$s</strong> or %2$s it</p>',
					esc_html( APP_PATH ),
					/* html */ '<a href="https://getcomposer.org/" target="_blank">install</a>'
				),
			);
		}
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
		if (!$this->webRoutesWasLoaded) {
			$this->webRoutesWasLoaded = true;

			/**
			 * Include routes files
			 */
			File::requireOnce(BROCOOLY_THEME_PATH . '/routes/web.php');
			File::requireOnce(BROCOOLY_THEME_PATH . '/routes/ajax.php');

			/**
			 * Resolve routes
			 */
			$this->router->resolve();
		}
	}

	/**
	 * Boot loaders.
	 * Make instance of this loader
	 *
	 * @param array $loaders | array of app loaders.
	 */
	public function run()
	{
		if (!$this->booted) {
			foreach ($this->loaders as $loader) {
				if (method_exists($loader, 'call')) {
					$this->call([$loader, 'call']);
				}
			}
			$this->booted = true;
		}
	}
}
