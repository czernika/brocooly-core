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
 * @method static array association()
 * @method static array checkbox()
 * @method static array color()
 * @method static array complex()
 * @method static array date()
 * @method static array dateTime()
 * @method static array file()
 * @method static array footerScripts()
 * @method static array gravityForm()
 * @method static array headerScripts()
 * @method static array hidden()
 * @method static array html()
 * @method static array image()
 * @method static array map()
 * @method static array mediaGallery()
 * @method static array multiset()
 * @method static array oembed()
 * @method static array radio()
 * @method static array radioImage()
 * @method static array richText()
 * @method static array select()
 * @method static array separator()
 * @method static array set()
 * @method static array text()
 * @method static array textarea()
 * @method static array time()
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
