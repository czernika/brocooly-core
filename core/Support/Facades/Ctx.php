<?php
/**
 * Context facade
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Facades;

use Brocooly\Storage\Context;

class Ctx extends AbstractFacade
{
	protected static $factory = Context::class;
}
