<?php
/**
 * Adds new shortcodes.
 *
 * @see https://developer.wordpress.org/reference/functions/add_shortcode/
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Brocooly\App;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

class ShortcodeServiceProvider extends AbstractService
{

	private array $shortcodes;

	public function __construct( App $app ) {
		$this->shortcodes = config( 'views.shortcodes', [] );

		parent::__construct( $app );
	}

	/**
	 * Create shortcodes
	 *
	 * @throws InvalidArgumentException Shortcodes type is not an array.
	 * @throws InvalidArgumentException Shortcode id is not string or empty string.
	 * @throws InvalidArgumentException Shortcode method render does not exists.
	 * @return void
	 */
	public function boot() {
		Assert::isArray(
			$this->shortcodes,
			/* translators: 1: type of variable */
			sprintf(
				'`views.shortcodes` key must be an array, %s given',
				gettype( $this->shortcodes )
			)
		);

		foreach ( $this->shortcodes as $shortcodeClass ) {
			$shortcode = $this->app->get( $shortcodeClass );

			$this->checkShortcode( $shortcode, $shortcodeClass );

			/**
			 * When adding `add_shortcode()` function in a plugin,
			 * it’s good to add it in a function that is hooked to `init` hook.
			 * So that WordPress has time to initialize properly.
			 */
			add_action(
				'init',
				function() use ( $shortcode ) {
					add_shortcode(
						Str::snake( $shortcode::SHORTCODE_ID ),
						[ $shortcode, 'render' ]
					);
				}
			);
		}
	}

	private function checkShortcode( $shortcode, $shortcodeClass ) {
		Assert::stringNotEmpty(
			$shortcode::SHORTCODE_ID,
			/* translators: 1: shortcode class name */
			sprintf(
				'ID property was not provided for %s shortcode',
				$shortcodeClass
			)
		);

		Assert::methodExists(
			$shortcode,
			'render',
			/* translators: 1: shortcode class name */
			sprintf(
				'%s shortcode must have `render()` method',
				$shortcodeClass
			)
		);
	}
}
