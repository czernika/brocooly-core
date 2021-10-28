<?php
/**
 * Metaboxes facade
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Facades;

/**
 * @method static object association()
 * @method static object checkbox()
 * @method static object color()
 * @method static object complex()
 * @method static object date()
 * @method static object dateTime()
 * @method static object file()
 * @method static object footerScripts()
 * @method static object gravityForm()
 * @method static object headerScripts()
 * @method static object hidden()
 * @method static object html()
 * @method static object image()
 * @method static object map()
 * @method static object mediaGallery()
 * @method static object multiset()
 * @method static object oembed()
 * @method static object radio()
 * @method static object radioImage()
 * @method static object richText()
 * @method static object select()
 * @method static object separator()
 * @method static object set()
 * @method static object text()
 * @method static object textarea()
 * @method static object time()
 */
class Meta extends AbstractFacade
{
	/**
	 * Meta Facade
	 *
	 * @return string
	 */
	protected static function accessor() {
		return 'meta';
	}
}
