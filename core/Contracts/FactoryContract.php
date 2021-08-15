<?php
/**
 * Factory interface
 * Each factory MUST have static create method
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Contracts;

interface FactoryContract
{

	/**
	 * Call function
	 *
	 * @param string $name | method name.
	 * @param array  $arguments | method arguments.
	 * @return mixed
	 */
	public static function create( string $name, array $arguments, $factory = null );
}
