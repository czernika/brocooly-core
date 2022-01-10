<?php
/**
 * App interface
 *
 * @package Brocooly-core 
 * @since Brocooly 0.14.0
 * @since 1.1.0
 */

declare(strict_types=1);

namespace Brocooly\Contracts;

use Psr\Container\ContainerInterface;

interface AppContainerInterface extends ContainerInterface
{
	public function web();

	public function ajax();

	public function run();
}
