<?php
/**
 * Set Debuggers for Application
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Loaders;

use Whoops\Run;
use Brocooly\App;
use HelloNico\Twig\DumpExtension;

class DebuggerLoader
{
	/**
	 * Application instance
	 *
	 * @var instanceof Brocooly\App
	 */
	protected App $app;

	public function __construct( App $app ) {
		$this->app = $app;
	}

	/**
	 * Register Debuggers for both views and backend
	 */
	public function call() {

		/**
		 * Twig minimizer
		 */
		add_filter(
			'timber/loader/twig',
			function( $twig ) {
				$twig->addExtension( new \nochso\HtmlCompressTwig\Extension( isProduction() ) );
				return $twig;
			}
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

			/**
			 * Twig debugger
			 */
			add_filter(
				'timber/loader/twig',
				function( $twig ) {
					$twig->addExtension( $this->app->get( DumpExtension::class ) );
					return $twig;
				}
			);

			/**
			 * Timber commented output
			 */
			if ( function_exists( '\Djboris88\Timber\initialize_filters' ) && config( 'debug.comment_output' ) ) {
				\Djboris88\Timber\initialize_filters();
			}

			/**
			 * Application debugger
			 */
			$handler = config( 'debug.handler' );
			if ( $handler ) {
				$whoops = $this->app->make( Run::class );
				$whoops->pushHandler( $this->app->get( $handler ) );
				$whoops->register();
			}
		}
	}

}

