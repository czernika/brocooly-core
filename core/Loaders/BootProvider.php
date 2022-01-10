<?php
/**
 * Boot Service Provider
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Loaders;

class BootProvider extends ProviderLoader
{

	/**
	 * Call boot method
	 */
	public function call() {
		$this->run( 'boot' );
	}
}
