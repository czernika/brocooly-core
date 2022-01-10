<?php
/**
 * Register Service Provider
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Loaders;

class RegisterProvider extends ProviderLoader
{

	/**
	 * Call register method
	 */
	public function call() {
		$this->run( 'register' );
	}
}
