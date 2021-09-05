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

use DI\Container;
use Timber\Timber;
use Brocooly\Support\Facades\File;
use Brocooly\Loaders\HookLoader;
use Brocooly\Loaders\AssetsLoader;
use Brocooly\Loaders\BootProvider;
use Brocooly\Loaders\ConfigLoader;
use Brocooly\Loaders\DebuggerLoader;
use Brocooly\Loaders\DefinitionLoader;
use Brocooly\Loaders\RegisterProvider;
use Psr\Container\ContainerInterface;
use Brocooly\Contracts\AppContainerInterface;

use function DI\factory;
use function DI\autowire;

class App implements AppContainerInterface
{

	/**
	 * Timber instance
	 *
	 * @var instanceof Timber\Timber
	 */
	public Timber $timber;

	/**
	 * DI Container
	 *
	 * @var instanceof \DI\Container
	 */
	private Container $container;

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

	public function __construct(Container $c)
	{

		$this->checkMinRequirements();

		$this->container = $c;
		static::$app     = $this;
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

	/**
	 * Bind Interface with it's class object
	 *
	 * @inheritdoc
	 */
	public function bind(string $key, string $value)
	{
		$this->container->set(
			$key,
			factory(
				function (ContainerInterface $container) use ($value) {
					return $container->get($value);
				}
			)
		);
	}

	/**
	 * Wire key with it's value using DI\Container
	 *
	 * @inheritdoc
	 */
	public function wire(string $key, string $value)
	{
		$this->container->set($key, autowire($value));
	}

	/**
	 * Get key from App Container
	 *
	 * @param string $key | key to get.
	 * @return mixed
	 */
	public function get($id)
	{
		return $this->container->get($id);
	}

	/**
	 * Set value into App Container
	 *
	 * @param string $key | key name.
	 * @param mixed  $value | key value.
	 */
	public function set($key, $value)
	{
		$this->container->set($key, $value);
	}

	/**
	 * Check if is value exists in App Container
	 *
	 * @param string $key | key to check.
	 * @return boolean
	 */
	public function has($key)
	{
		return $this->container->has($key);
	}

	/**
	 * Inject dependencies into object
	 *
	 * @param $object | instance of class to inject on.
	 */
	public function injectOn($object)
	{
		return $this->container->injectOn($object);
	}

	/**
	 * Create instance of object
	 *
	 * @param string $name | object name.
	 * @param array  $parameters | additional data to pass.
	 * @return object
	 */
	public function make($name, $parameters = [])
	{
		return $this->container->make($name, $parameters);
	}

	/**
	 * @inheritDoc
	 */
	public function call($callable, $parameters = [])
	{
		return $this->container->call($callable, $parameters);
	}
}
