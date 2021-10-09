<?php
/**
 * Menu Service Provider
 * Registers a navigation menu location for a theme.
 *
 * @see https://developer.wordpress.org/reference/functions/register_nav_menu/
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Timber\Menu;
use Timber\Timber;
use Webmozart\Assert\Assert;
use Brocooly\Support\Facades\Ctx;

class MenuServiceProvider extends AbstractService
{

	/**
	 * Register menus
	 */
	public function register() {
		$this->app->set( 'menus', config( 'menus.menus', [] ) );
		$this->app->set( 'menus_postfix', config( 'menus.postfix', '_menu' ) );
	}

	/**
	 * Create menu instances
	 */
	public function boot() {
		$menus = $this->app->get( 'menus' );

		Assert::isArray(
			$menus,
			/* translators: 1 - type of variable */
			sprintf(
				'`app.menus` key must be an array, %s given',
				gettype( $menus )
			)
		);

		if ( ! empty( $menus ) ) {
			foreach ( $menus as $menuClass ) {
				$menu = $this->app->get( $menuClass );

				Assert::stringNotEmpty(
					$menu::LOCATION,
					/* translators: 1 - menu class */
					sprintf(
						'Name property was not provided for %s menu',
						$menuClass
					)
				);

				Assert::methodExists(
					$menu,
					'label',
					/* translators: 1 - menu class name */
					sprintf(
						'%s menu must have `label()` method which should return string',
						$menuClass
					)
				);

				/**
				 * Register menu in WordPress
				 */
				add_action(
					'after_setup_theme',
					function() use ( $menu ) {
						register_nav_menu( $menu::LOCATION, $menu->label() );
					}
				);
			}
		}
	}
}
