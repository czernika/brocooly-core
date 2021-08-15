<?php
/**
 * Abstract factory
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Factories;

use Brocooly\Contracts\FactoryContract;

class AbstractFactory implements FactoryContract
{
	/**
	 * @inheritDoc
	 */
	public static function create( string $name, array $arguments, $factory = null ) {
		return call_user_func_array( $name, $arguments );
	}
}
