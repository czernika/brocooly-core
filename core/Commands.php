<?php
/**
 * Console commands loader
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly;

use Brocooly\Console\ClearCache;
use Brocooly\Console\MakeController;
use Brocooly\Console\MakeCustomizerOption;
use Brocooly\Console\MakeCustomizerPanel;
use Brocooly\Console\MakeCustomizerSection;
use Brocooly\Console\MakeHook;
use Brocooly\Console\MakeMail;
use Brocooly\Console\MakeMenu;
use Brocooly\Console\MakeModelComment;
use Brocooly\Console\MakeModelPostType;
use Brocooly\Console\MakeModelRole;
use Brocooly\Console\MakeModelTaxonomy;
use Brocooly\Console\MakeModelUser;
use Brocooly\Console\MakeTask;

class Commands
{
	/**
	 * Array of available console commands
	 *
	 * @var array
	 */
	private static array $commands = [
		MakeCustomizerOption::class,
		MakeCustomizerSection::class,
		MakeCustomizerPanel::class,
		MakeController::class,
		MakeHook::class,
		MakeMenu::class,
		MakeMail::class,
		MakeTask::class,
		MakeModelPostType::class,
		MakeModelTaxonomy::class,
		MakeModelUser::class,
		MakeModelRole::class,
		MakeModelComment::class,
		ClearCache::class,
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
