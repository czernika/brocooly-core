<?php
/**
 * Widget Service Provider
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Brocooly\App;
use Timber\Timber;

class WidgetServiceProvider extends AbstractService
{

	private array $sidebars;

	private array $widgets;

	private bool $loadDefaults;

	private bool $useGutenberg;

	public function __construct( App $app ) {
		$this->sidebars      = config( 'widgets.sidebars', [] );
		$this->widgets       = config( 'widgets.widgets', [] );
		$this->loadDefaults  = config( 'widgets.loadDefaults', false );
		$this->useGutenberg  = config( 'widgets.useGutenberg', false );

		parent::__construct( $app );
	}

	/**
	 * Create sidebars and widgets
	 */
	public function boot() {

		if ( ! $this->loadDefaults ) {
			remove_action( 'init', 'wp_widgets_init', 1 );
			add_action( 'init', function() { do_action( 'widgets_init' ); }, 1 );
		}

		/**
		 * Use block editor or not
		 *
		 * @since 0.8.4
		 */
		if ( ! $this->useGutenberg ) {
			add_action(
				'after_setup_theme',
				function() {
					remove_theme_support( 'widgets-block-editor' );
				}
			);
		}

		$this->bootSidebars();
		$this->bootWidgets();
	}

	/**
	 * Register sidebars and add theme into Timber context
	 */
	private function bootSidebars() {
		foreach ( $this->sidebars as $sidebarClass ) {
			$sidebar = $this->app->make( $sidebarClass );

			add_action(
				'widgets_init',
				function() use ( $sidebar ) {
					$defaults      = [
						'before_widget' => '<li id="%1$s" class="widget %2$s">',
						'after_widget'  => '</li>',
						'before_title'  => '<h2 class="widgettitle">',
						'after_title'   => '</h2>',
					];
					$options       = array_merge( $sidebar->options(), $defaults );
					$options['id'] = $sidebar::SIDEBAR_ID;
					register_sidebar( $options );
				}
			);
		}
	}

	/**
	 * Register widgets
	 */
	private function bootWidgets() {
		foreach ( $this->widgets as $widget ) {
			add_action(
				'widgets_init',
				function() use ( $widget ) {
					register_widget( $widget );
				}
			);
		}
	}
}
