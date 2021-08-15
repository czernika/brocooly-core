<?php
/**
 * Metaboxes facade
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Facades;

class Meta extends AbstractFacade
{
	/**
	 * Meta Facade
	 *
	 * @return string
	 */
	protected static function accessor() {
		return 'meta';
	}
}
