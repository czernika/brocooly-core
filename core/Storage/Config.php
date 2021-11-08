<?php
/**
 * Application Config instance
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Storage;

use Illuminate\Support\Arr;

class Config
{

	/**
	 * Configuration data
	 *
	 * @var array
	 */
	public static array $data = [];

	/**
	 * Set config value
	 *
	 * @param string $key | key name.
	 * @param mixed  $value | key value.
	 * @return void
	 */
	public static function set( string $key, $value ) {
		Arr::set( static::$data, $key, $value );
	}

	/**
	 * Get configuration value by key
	 * If no key passed return whole array
	 *
	 * @param string|null $key | key to get.
	 * @return array|mixed
	 */
	public static function get( string $key = null ) {
		if ( null === $key ) {
			return static::$data;
		}

		return Arr::get( static::$data, $key );
	}

	/**
	 * Remove key from data
	 *
	 * @param string $key | key to delete.
	 * @return void
	 */
	public static function delete( string $key ) {
		Arr::pull( static::$data, $key );
	}
}
