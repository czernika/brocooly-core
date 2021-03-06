<?php
/**
 * Resolve custom post type
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Factories;

class PostTypeFactory extends AbstractFactory
{

	/**
	 * Get post type model
	 *
	 * @param string|null $model | model accessor.
	 * @return object
	 */
	public static function model() {
		$modelObject = app( get_post_type() );
		return $modelObject;
	}
}
