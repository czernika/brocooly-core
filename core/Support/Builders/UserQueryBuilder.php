<?php
/**
 * Users query handler.
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Builders;

use Illuminate\Support\Arr;

class UserQueryBuilder
{

	/**
	 * Role name
	 *
	 * @var string
	 */
	private string $role;

	/**
	 * Authenticated User object
	 *
	 * @var object|null
	 */
	private ?object $auth = null;

	/**
	 * User object
	 *
	 * @var object|null
	 */
	private ?object $user = null;

	/**
	 * Users collection
	 *
	 * @var array
	 */
	private array $userCollection = [];

	/**
	 * User query
	 *
	 * @var array
	 */
	private array $usersQuery = [];

	public function __construct( string $role ) {
		$this->role = $role;
		$this->auth = get_current_user_id() ?
						$this->getUser( get_current_user_id() ) :
						null;

		if ( 'user' !== $this->role ) {
			$this->usersQuery['role'] = $this->role;
		}
	}

	private function getUser( int $id ) {
		$user = app()->make( 'users.parent', [ 'uid' => $id ] );
		return $user;
	}

	public function auth() {
		return $this->find( $this->auth->ID );
	}

	public function find( int $id ) {
		$this->user = $this->getUser( $id );
		return $this;
	}

	public function exists() {
		return (bool) $this->user?->ID;
	}

	public function hasRole( string $role ) {
		$userRoles = $this->auth?->roles;
		if ( $this->user ) {
			$userRoles = $this->user->roles;
		}
		return Arr::exists( (array) $userRoles, $role );
	}

	public function hasAnyRole( array $roles ) {
		$userRoles = array_keys( $this->auth?->roles );
		if ( $this->user ) {
			$userRoles = array_keys( $this->user->roles );
		}

		foreach ( $roles as $role ) {
			if ( in_array( $role, (array) $userRoles, true ) ) {
				return true;
			}
		}

		return false;
	}

	public function getRoles() {
		if ( $this->user ) {
			return $this->user->roles;
		}

		return $this->auth?->roles;
	}

	public function can( $cap ) {
		if ( $this->user ) {
			return $this->user->can( $cap );
		}

		return $this->auth?->can( $cap );
	}

	private function getUsers( array $args = [] ) {
		$this->usersQuery = wp_parse_args( $args, $this->usersQuery );
		return get_users( $this->usersQuery );
	}

	public function all() {
		return $this->get();
	}

	public function where( string $key, $value ) {
		$query            = [ $key => $value ];
		$this->usersQuery = wp_parse_args( $query, $this->usersQuery );
		return $this;
	}

	public function withRoles( $role ) {
		return $this->where( 'role', $role );
	}

	public function get() {
		if ( $this->user ) {
			return $this->user;
		}

		foreach ( $this->getUsers() as $userId ) {
			$this->userCollection[] = $this->getUser( $userId );
		}
		return $this->userCollection;
	}

}

