<?php
/**
 * Users query handler.
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Builders;

class UserQueryBuilder
{

	/**
	 * Role name
	 *
	 * @var string
	 */
	private string $role;

	/**
	 * User object
	 *
	 * @var object|null
	 */
	private ?object $auth;

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

	private function getUser( $id ) {
		$user = app()->make( 'users.parent', [ 'uid' => $id ] );
		return $user;
	}

	private function getUsers( array $args = [] ) {
		$this->usersQuery = wp_parse_args( $args, $this->usersQuery );
		return get_users( $this->usersQuery );
	}

	public function auth() {
		return $this->auth;
	}

	public function find( $id ) {
		$user = $this->getUser( $id );
		return (bool) $user->ID ? $user : null;
	}

	public function exists( $id ) {
		$user = $this->getUser( $id );
		return (bool) $user->ID;
	}

	public function all() {
		return $this->get();
	}

	public function where( $key, $value ) {
		$query            = [ $key => $value ];
		$this->usersQuery = wp_parse_args( $query, $this->usersQuery );
		return $this;
	}

	public function role( $role ) {
		return $this->where( 'role', $role );
	}

	public function get() {
		foreach ( $this->getUsers() as $userId ) {
			$this->userCollection[] = $this->getUser( $userId );
		}
		return $this->userCollection;
	}

}
