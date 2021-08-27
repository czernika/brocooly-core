<?php
/**
 * User instance
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Models\Users;

use Carbon_Fields\Container;
use Timber\User as TimberUser;
use Brocooly\Support\Builders\UserQueryBuilder;

abstract class User extends TimberUser
{

	/**
	 * Role name
	 *
	 * @var string
	 */
	const ROLE = 'user';

	/**
	 * Return role name in human readable format
	 *
	 * @return string
	 */
	public function label() {
		return esc_html( 'Custom role', 'brocooly' );
	}

	/**
	 * Get user capabilities
	 *
	 * @return array
	 */
	public function capabilities() {
		return [];
	}

	/**
	 * Return user's capabilities same as already registered role.
	 *
	 * @param string $role | role name.
	 * @return \WP_Role|null
	 */
	protected function as( string $role ) {
		$roleCaps = get_role( $role );
		if ( $roleCaps ) {
			$caps = $roleCaps->capabilities;
			return $caps;
		}

		return null;
	}

	private static function getRoleObject() {
		return get_role( static::ROLE );
	}

	protected static function getRole() {
		return static::getRoleObject();
	}

	protected static function getRoleName() {
		return static::getRole()->name;
	}

	protected static function getRoleCapabilities() {
		return static::getRole()->capabilities;
	}

	protected static function addRoleCap( $caps ) {
		static::getRole()->add_cap( $caps );
	}

	protected static function removeRoleCap( $caps ) {
		static::getRole()->remove_cap( $caps );
	}

	/**
	 * Create metabox container for post types
	 *
	 * @param string $id | container id.
	 * @param string $label | container label.
	 * @param array  $fields | array of custom fields.
	 * @return void
	 */
	protected function createFields( string $id, string $label, array $fields ) {
		$this->setContainer( $id, $label )
			->add_fields( $fields );
	}

	/**
	 * Set metabox container
	 *
	 * @param string $id | container id.
	 * @param string $label | container label.
	 * @return object
	 */
	protected function setContainer( string $id, string $label ) {
		$container = Container::make( 'user_meta', $id, $label );
		return $container;
	}

	/**
	 * Get users by query
	 *
	 * @param string $name | method name.
	 * @param array  $arguments | method options.
	 * @return void
	 */
	public static function __callStatic( string $name, array $arguments ) {
		return app()->make(
			UserQueryBuilder::class,
			[ 'role' => static::ROLE, 'user' => new static() ],
		)->$name( ...$arguments );
	}

	/**
	 * Create user or update if ID passed.
	 *
	 * @param array $userdata | additional user data.
	 * @return int|\WP_Error
	 */
	public static function insert( array $userdata ) {
		return wp_insert_user( $userdata );
	}

	/**
	 * Create user.
	 *
	 * @param string $username | username.
	 * @param string $password | password.
	 * @param string $email | email.
	 * @return int|\WP_Error
	 */
	public static function create( string $username, string $password, string $email = '' ) {
		return wp_create_user( $username, $password, $email );
	}

}
