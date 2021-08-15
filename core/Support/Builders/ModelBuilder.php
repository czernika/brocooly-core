<?php
/**
 * Resolve post type object
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Builders;

use Brocooly\Support\Factories\PostTypeFactory;

class ModelBuilder
{
	public function resolve() {
		return PostTypeFactory::model();
	}
}
