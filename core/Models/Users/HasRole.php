<?php

declare(strict_types=1);

namespace Brocooly\Models\Users;

trait HasRole
{


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
	 * @return array|null
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

}
