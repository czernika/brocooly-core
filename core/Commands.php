<?php
/**
 * Console commands loader
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly;

use Brocooly\Console\MakeController;
use Brocooly\Console\MakeCustomizerOption;
use Brocooly\Console\MakeCustomizerPanel;
use Brocooly\Console\MakeCustomizerSection;
use Brocooly\Console\MakeHook;
use Brocooly\Console\MakeMail;
use Brocooly\Console\MakeMenu;

class Commands
{
	/**
	 * Array of available console commands
	 *
	 * @var array
	 */
	private static array $commands = [
		MakeController::class,
		MakeCustomizerOption::class,
		MakeCustomizerPanel::class,
		MakeCustomizerSection::class,
		MakeHook::class,
		MakeMenu::class,
		MakeMail::class,
	];

	/**
	 * Get all console commands list
	 *
	 * @return array
	 */
	public static function get() {
		return static::$commands;
	}
}
