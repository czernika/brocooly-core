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

use Brocooly\App;
use Timber\Menu;
use Timber\Timber;
use Webmozart\Assert\Assert;
use Brocooly\Support\Facades\Ctx;

class MenuServiceProvider extends AbstractService
{

	private array $menus;

	public function __construct( App $app ) {
		$this->menus = config( 'menus.menus', [] );

		parent::__construct( $app );
	}

	/**
	 * Create menu instances
	 */
	public function boot() {
		Assert::isArray(
			$this->menus,
			/* translators: 1 - type of variable */
			sprintf(
				'`app.menus` key must be an array, %s given',
				gettype( $this->menus )
			)
		);

		foreach ( $this->menus as $menuClass ) {
			$menu = $this->app->get( $menuClass );

			$this->checkMenu( $menu, $menuClass );

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

	private function checkMenu( $menu, $menuClass ) {
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
	}
}
