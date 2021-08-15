<?php
/**
 * Abstract middleware
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Http\Middleware;

abstract class AbstractMiddleware
{

	/**
	 * Logic behind middleware.
	 * This function will fire on Controller init
	 *
	 * @return void
	 */
	abstract public function handle();
}
