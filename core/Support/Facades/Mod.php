<?php
/**
 * Customizer facade
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Facades;

/**
 * @method static array background()
 * @method static array checkbox()
 * @method static array code()
 * @method static array colorPalette()
 * @method static array custom()
 * @method static array dashicons()
 * @method static array date()
 * @method static array dimension()
 * @method static array dimensions()
 * @method static array dropdownPages()
 * @method static array editor()
 * @method static array generic()
 * @method static array image()
 * @method static array link()
 * @method static array multicheck()
 * @method static array multicolor()
 * @method static array number()
 * @method static array palette()
 * @method static array radioButtonset()
 * @method static array radioImage()
 * @method static array radio()
 * @method static array repeater()
 * @method static array select()
 * @method static array slider()
 * @method static array sortable()
 * @method static array switch()
 * @method static array text()
 * @method static array textarea()
 * @method static array toggle()
 * @method static array typography()
 * @method static array upload()
 */
class Mod extends AbstractFacade
{
	/**
	 * Meta Facade
	 *
	 * @return string
	 */
	protected static function accessor() {
		return 'mod';
	}
}
