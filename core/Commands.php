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
use Brocooly\Console\MakeGutenberg;
use Brocooly\Console\MakeHook;
use Brocooly\Console\MakeMail;
use Brocooly\Console\MakeMenu;
use Brocooly\Console\MakeMiddleware;
use Brocooly\Console\MakeModelComment;
use Brocooly\Console\MakeModelPostType;
use Brocooly\Console\MakeModelRole;
use Brocooly\Console\MakeModelTaxonomy;
use Brocooly\Console\MakeModelUser;
use Brocooly\Console\MakeProvider;
use Brocooly\Console\MakeRequest;
use Brocooly\Console\MakeRule;
use Brocooly\Console\MakeSeeder;
use Brocooly\Console\MakeShortcode;
use Brocooly\Console\MakeSidebar;
use Brocooly\Console\MakeTask;
use Brocooly\Console\MakeTemplate;
use Brocooly\Console\MakeWidget;
use Brocooly\Console\Seed;

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
		MakeMiddleware::class,
		MakeRequest::class,
		MakeProvider::class,
		MakeHook::class,
		MakeTask::class,
		MakeMenu::class,
		MakeMail::class,
		MakeTemplate::class,
		MakeSidebar::class,
		MakeWidget::class,
		MakeShortcode::class,
		MakeGutenberg::class,
		MakeModelPostType::class,
		MakeModelTaxonomy::class,
		MakeModelUser::class,
		MakeModelRole::class,
		MakeModelComment::class,
		MakeSeeder::class,
		MakeRule::class,
		ClearCache::class,
		Seed::class,
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
