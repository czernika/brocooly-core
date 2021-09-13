<?php

declare(strict_types=1);

namespace Brocooly\Support\Facades;

use Brocooly\Storage\Option as StorageOption;

class Option extends AbstractFacade
{
	protected static $factory = StorageOption::class;
}
