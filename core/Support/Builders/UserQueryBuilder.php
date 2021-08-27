<?php
/**
 * Users query handler.
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Builders;

use Brocooly\Models\Users\User;

class UserQueryBuilder
{

	private string $role;

	private User $user;

	private array $usersQuery = [];

	public function __construct( string $role, User $user ) {
		$this->role = $role;
		$this->user = $user;

		if ( 'role' !== $this->role ) {
			$this->usersQuery['role'] = $this->role;
		}
	}

	/**
	 * Get all users
	 *
	 * @return array
	 */
	public function all() {
		return get_users( $this->usersQuery );
	}

	/**
	 * Get user with specific parameters.
	 *
	 * @param array $args | arguments for users to find.
	 * @return array
	 * @since 1.1.0
	 */
	public function with( array $args ) {
		$queryArgs = array_merge( $this->usersQuery, $args );
		return get_users( $queryArgs );
	}

	/**
	 * Get user by specific parameters.
	 *
	 * @param array $args | arguments for users to find.
	 * @return array
	 */
	public function getBy( array $args ) {
		return get_users( $args );
	}

	/**
	 * Get current user object
	 *
	 * @return User
	 */
	public function current() {
		return $this->user;
	}

	/**
	 * Get user by key.
	 *
	 * @param string $key | key to find.
	 * @param mixed  $value | value to get by.
	 * @return \WP_User
	 */
	public function where( string $key, $value ) {
		return get_user_by( $key, $value );
	}

	/**
	 * Get user by id
	 *
	 * @param integer $id | user id.
	 * @return \WP_User|false
	 */
	public function find( int $id ) {
		return get_userdata( $id );
	}

	/**
	 * Get current authenticated user or false.
	 *
	 * @return \WP_User|false
	 */
	public function auth() {
		if ( is_user_logged_in() ) {
			return $this->current();
		}

		return false;
	}

}
