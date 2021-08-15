<?php
/**
 * Database query facade.
 * Allows you to cooperate with WordPress database via wpdb class
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Facades;

use Brocooly\Support\Builders\DBQueryBuilder;

class DB extends AbstractFacade
{
	protected static $factory = DBQueryBuilder::class;
}
