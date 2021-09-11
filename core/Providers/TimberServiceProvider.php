<?php
/**
 * Timber Service Provider
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Twig\TwigFilter;
use Twig\TwigFunction;

class TimberServiceProvider extends AbstractService
{

	/**
	 * Register Timber config
	 */
	public function register() {
		$keys = [
			'views.dir'        => config( 'views.views', 'resources/views' ),
			'cache.path'       => config( 'views.cache' ) ? wp_normalize_path( config( 'views.cache' ) ) : false,
			'views.namespaces' => config( 'views.namespaces', [] ),
			'timber.filters'   => config( 'timber.filters', [] ),
			'timber.functions' => config( 'timber.functions', [] ),
		];
		foreach ( $keys as $key => $value ) {
			$this->app->set( $key, $value );
		}
	}

	/**
	 * Boot Timber options
	 */
	public function boot() {
		$this->app->timber::$dirname = $this->app->get( 'views.dir' );

		$this->addToTwig();
		$this->setLoader();
		$this->setCachePath();
	}

	/**
	 * Add custom filters and function to Twig
	 */
	private function addToTwig() {
		$functions = $this->app->get( 'timber.functions' );
		$filters   = $this->app->get( 'timber.filters' );

		add_filter(
			'timber/twig',
			function( $twig ) use ( $functions, $filters ) {
				if ( ! empty( $functions ) ) {
					foreach ( $functions as $name => $callback ) {

						if ( is_numeric( $name ) ) {
							$name = $callback;
						}

						$twig->addFunction( new TwigFunction( $name, $callback ) );
					}
				}

				if ( ! empty( $filters ) ) {
					foreach ( $filters as $name => $callback ) {

						if ( is_numeric( $name ) ) {
							$name = $callback;
						}

						$twig->addFilter( new TwigFilter( $name, $callback ) );
					}
				}

				return $twig;
			}
		);
	}

	/**
	 * Set custom Twig namespaces
	 */
	private function setLoader() {
		$namespaces = $this->app->get( 'views.namespaces' );
		add_filter(
			'timber/loader/loader',
			function( $loader ) use ( $namespaces ) {
				if ( ! empty( $namespaces ) ) {
					foreach ( $namespaces as $namespace => $path ) {
						$loader->addPath(
							trailingslashit( get_theme_file_path( $this->app->get( 'views.dir' ) ) ) . $path,
							$namespace
						);
					}
				}
				return $loader;
			}
		);
	}

	/**
	 * Set cache path
	 */
	private function setCachePath() {
		$cache = $this->app->get( 'cache.path' );

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
