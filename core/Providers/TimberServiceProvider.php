<?php
/**
 * Timber Service Provider
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Brocooly\App;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TimberServiceProvider extends AbstractService
{

	private string $dirname;

	private array $functions;

	private array $filters;

	private array $namespaces;

	private string|bool $cache;

	public function __construct( App $app ) {
		$this->dirname    = config( 'views.views', 'resources/views' );
		$this->functions  = config( 'timber.functions', [] );
		$this->filters    = config( 'timber.filters', [] );
		$this->namespaces = config( 'views.namespaces', [] );
		$this->cache      = config( 'views.cache', BROCOOLY_THEME_PATH . 'storage/cache/' );

		parent::__construct( $app );
	}

	/**
	 * Boot Timber options
	 */
	public function boot() {
		$this->app->timber::$dirname = $this->dirname;

		$this->addToTwig();
		$this->setLoader();
		$this->setCachePath();
	}

	/**
	 * Add custom filters and function to Twig
	 */
	private function addToTwig() {
		add_filter(
			'timber/twig',
			function( $twig ) {
				foreach ( $this->functions as $name => $callback ) {

					if ( is_numeric( $name ) ) {
						$name = $callback;
					}

					$twig->addFunction( new TwigFunction( $name, $callback ) );
				}

				foreach ( $this->filters as $name => $callback ) {

					if ( is_numeric( $name ) ) {
						$name = $callback;
					}

					$twig->addFilter( new TwigFilter( $name, $callback ) );
				}

				return $twig;
			}
		);
	}

	/**
	 * Set custom Twig namespaces
	 */
	private function setLoader() {
		add_filter(
			'timber/loader/loader',
			function( $loader ) {
				foreach ( $this->namespaces as $namespace => $path ) {

					if ( is_numeric( $namespace ) ) {
						$namespace = $path;
					}

					$loader->addPath(
						trailingslashit( get_theme_file_path( $this->dirname ) ) . $path,
						$namespace
					);
				}

				return $loader;
			}
		);
	}

	/**
	 * Set cache path
	 */
	private function setCachePath() {
		$cache = $this->cache ? wp_normalize_path( $this->cache ) : false;

		if ( (bool) $cache && isProduction() ) {
			if ( isTimberNext() ) {
				add_filter(
					'timber/twig/environment/options',
					function( $options ) use ( $cache ) {
						$options['cache'] = $cache;
						return $options;
					},
				);
			} else {
				$this->app->timber::$cache = true;
				add_filter(
					'timber/cache/location',
					function( $twig_cache ) use ( $cache ) {
						return untrailingslashit( $cache ) . '/views/';
					},
				);
			}
		}
	}
}

