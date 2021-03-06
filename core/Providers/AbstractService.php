<?php
/**
 * Abstract Service Provider instance
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Brocooly\App;

abstract class AbstractService
{
	/**
	 * Application instance
	 *
	 * @var object
	 */
	protected App $app;

	public function __construct( App $app ) {
		$this->app = $app;
	}
}
