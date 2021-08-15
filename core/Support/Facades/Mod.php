<?php
/**
 * Customizer facade
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Facades;

class Mod extends AbstractFacade
{
	/**
	 * Meta Facade
	 *
	 * @return string
	 */
	protected static function accessor() {
		return 'mod';
	}
}
