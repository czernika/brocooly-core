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

/**
 * @method static array all()
 * @method static array get()
 * @method static $this where( $key, $value )
 * @method static $this role( $role )
 * @method static array getRoles()
 * @method static bool hasRole( string $role )
 * @method static bool hasAnyRole( array $roles )
 * @method static bool can( $capabilities )
 * @method static User|null find( $id )
 * @method static User|null auth()
 * @method static bool exists( $id )
 */
abstract class User extends TimberUser
{

	use HasRole;

	/**
	 * Role name
	 *
	 * @var string
	 */
	const ROLE = 'user';

	/**
	 * Get array of protected meta calling as methods
	 *
	 * @var array
	 */
	protected array $allowedMeta = [];

	/**
	 * Get carbon meta or parent function
	 *
	 * @param string $field | method name.
	 * @param array $args | function arguments.
	 * @return mixed
	 */
	public function __call( $field, $args ) {
		if ( in_array( $field, $this->allowedMeta, true ) ) {
			return carbon_get_user_meta( $this->id, $field );
		}

		return parent::__call( $field, $args );
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
			[ 'role' => static::ROLE ],
		)->$name( ...$arguments );
	}

	/**
	 * Create user
	 *
	 * @param array $userdata | user data.
	 * @return int|\WP_Error
	 */
	public static function create( array $userdata ) {
		return wp_insert_user( $userdata );
	}

	public function delete( $reassign = null ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';
		return wp_delete_user( $this->id, $reassign );
	}

}

