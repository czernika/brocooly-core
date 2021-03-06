<?php
/**
 * Create metaboxes
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Factories;

use Carbon_Fields\Field;

class MetaFactory extends AbstractFactory
{
	/**
	 * Create metabox
	 * Use Carbon_Fields\Field class to create metaboxes
	 *
	 * @param string $name | metabox name.
	 * @param array  $arguments | metabox parameters.
	 * @return void
	 */
	public static function create( string $name, array $arguments, $factory = null ) {
		return Field::make( $name, ...$arguments );
	}
}
