<?php
/**
 * User and their roles service provider.
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Brocooly\App;

class UserServiceProvider extends AbstractService
{

	private array $roles;

	private $parent;

	public function __construct( App $app ) {
		$this->roles  = config( 'users.templates', [] );
		$this->parent = config( 'users.parent', null );

		parent::__construct( $app );
	}

	public function boot() {
		$this->registerUser();
		$this->registerRoles();
	}

	private function registerUser() {
		if ( $this->parent && class_exists( $this->parent ) ) {
			$user = $this->app->get( $this->parent );

			$this->callMetaFields( $user, 'fields' );
			$this->callMetaFields( $user, 'userAvatar' ); // avatar trait.
		}
	}

	private function callMetaFields( object $user, string $method ) {
		if ( method_exists( $user, $method ) ) {
			add_action(
				'carbon_fields_register_fields',
				[ $user, $method ],
			);
		}
	}

	/**
	 * Register and deregister custom user roles
	 *
	 * NOTE: When to call
	 * Make sure the global $wp_roles is available before attempting to add or modify a role. The best practice is to use a plugin (or theme) activation hook to make changes to roles (since you only want to do it once!).
	 *
	 * mu-plugins loads too early, so use an action hook (like 'init') to wrap your add_role() call if you’re doing this in the context of an mu-plugin.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_role/#user-contributed-notes
	 * @return void
	 */
	private function registerRoles() {
		foreach ( $this->roles as $roleClass ) {
			$role = $this->app->get( $roleClass );
			add_action(
				'after_switch_theme',
				function() use ( $role ) {

					if ( get_role( $role::ROLE ) ) {
						return;
					}

					add_role(
						$role::ROLE,
						$role->label(),
						$role->capabilities(),
					);
				}
			);

			add_action(
				'switch_theme',
				function() use ( $role ) {
					remove_role( $role::ROLE );
				}
			);
		}
	}
}
