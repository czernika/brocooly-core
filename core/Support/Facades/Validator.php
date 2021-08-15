<?php
/**
 * Validator facade
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Facades;

class Validator extends AbstractFacade
{
	/**
	 * Validator Facade
	 *
	 * @return string
	 */
	protected static function accessor() {
		return 'validator';
	}
}
