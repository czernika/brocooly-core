<?php
/**
 * Create customizer options
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Factories;

use Illuminate\Support\Str;

class CustomizerFactory extends AbstractFactory
{
	/**
	 * Create options for customizer
	 *
	 * @param string $type | customizer option type.
	 * @param array  $arguments | customizer option parameters.
	 * @return array
	 */
	public static function create( string $type, array $arguments, $factory = null ) {
		[ $setting, $options ] = $arguments;

		if ( is_string( $options ) ) {
			$opts          = (array) $options;
			$opts['label'] = $options;
		} else {
			$opts = $options;
		}

		$opts['settings'] = $setting;
		$opts['type']     = Str::kebab( $type );

		return $opts;
	}
}
