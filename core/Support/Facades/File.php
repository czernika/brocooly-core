<?php
/**
 * Filesystem facade
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Facades;

use Illuminate\Filesystem\Filesystem;

class File extends AbstractFacade
{
	protected static $factory = Filesystem::class;
}
