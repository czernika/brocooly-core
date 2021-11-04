<?php
/**
 * Register theme customizer options
 * We're using Kirki Framework plugin under MIT license
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Kirki;
use Webmozart\Assert\Assert;

class KirkiServiceProvider extends AbstractService
{

	private array $wpSections = [
		'title_tagline',
		'colors',
		'header_image',
		'background_image',
		'nav_menus',
		'widgets',
		'static_front_page',
		'custom_css',
	];

	/**
	 * Register customizer configuration
	 */
	public function register() {
		$customizerSettings = [ 'config', 'prefix', 'panels', 'sections', 'options' ];
		foreach ( $customizerSettings as $setting ) {
			$this->app->set( 'customizer.' . $setting, config( 'customizer.' . $setting ) );
		}
	}

	/**
	 * Create sections and options
	 */
	public function boot() {
		if ( class_exists( 'Kirki' ) ) {
			$this->initConfig();
			$this->initPanels();
			$this->initSections();
			$this->initOptions();
		}
	}

	/**
	 * Init Kirki configuration
	 *
	 * NOTE: currently there is only one config supported
	 */
	private function initConfig() {
		$configs = $this->app->get( 'customizer.config' );

		if ( ! empty( $configs ) ) {
			foreach ( $configs as $id => $options ) {
				Kirki::add_config( $id, $options );
			}
		}
	}

	/**
	 * Init customizer panel
	 */
	private function initPanels() {
		$panels = $this->app->get( 'customizer.panels' );

		if ( ! empty( $panels ) ) {
			foreach ( $panels as $panelClass ) {
				$panel = $this->app->get( $panelClass );

				$this->assertPanel( $panel, $panelClass );

				$options = $panel->options();
				if ( is_string( $options ) ) {
					$options = [ 'title' => $options ];
				}

				if ( ! in_array( $panel::PANEL_ID, $this->wpSections, true ) ) {
					new \Kirki\Panel( esc_html( $panel::PANEL_ID ), $options );
				}
			}
		}
	}

	/**
	 * Init customizer sections
	 */
	private function initSections() {
		$sections = $this->app->get( 'customizer.sections' );
		$prefix   = $this->app->get( 'customizer.prefix' );

		if ( ! empty( $sections ) ) {
			foreach ( $sections as $sectionClass ) {
				$section = $this->app->get( $sectionClass );

				$this->assertSection( $section, $sectionClass );

				$options = $section->options();
				if ( is_string( $options ) ) {
					$options = [ 'title' => $options ];
				}

				if ( ! in_array( $section::SECTION_ID, $this->wpSections, true ) ) {
					new \Kirki\Section( esc_html( $section::SECTION_ID ), $options );
				}

				foreach ( $section->controls() as $controls ) {
					$controls['args']['section']  = esc_html( $section::SECTION_ID );
					$controls['args']['settings'] = $prefix . $controls['args']['settings'];
					$type = '\\Kirki\\Field\\' . ucfirst( $controls['type'] );
					new $type( $controls['args'] );
				}
			}
		}
	}

	/**
	 * Init customizer controls
	 */
	private function initOptions() {
		$options = $this->app->get( 'customizer.options' );
		$prefix  = $this->app->get( 'customizer.prefix' );

		if ( ! empty( $options ) ) {
			foreach ( $options as $optionClass ) {
				$option   = $this->app->make( $optionClass );
				$settings = $option->settings();

				$this->assertOption( $option, $optionClass );

				$settings['args']['settings'] = $prefix . $settings['args']['settings'];

				$type = '\\Kirki\\Field\\' . ucfirst( $settings['type'] );
				new $type( $settings['args'] );
			}
		}
	}

	/**
	 * Assert panel options are OK
	 *
	 * @param object $panel | panel object.
	 * @param string $panelClass | panel class name.
	 * @throws AssertionError
	 */
	private function assertPanel( object $panel, string $panelClass ) {
		Assert::stringNotEmpty(
			esc_html( $panel::PANEL_ID ),
			/* translators: 1: customizer panel id. */
			sprintf(
				'You need to specify static `id` parameter for %s panel',
				$panelClass,
			),
		);
	}

	/**
	 * Assert section options are OK
	 *
	 * @param object $section | section object.
	 * @param string $sectionClass | section class name.
	 * @throws \Exception | Assert this is a valid section.
	 */
	private function assertSection( object $section, string $sectionClass ) {
		Assert::stringNotEmpty(
			esc_html( $section::SECTION_ID ),
			/* translators: 1: customizer section class name. */
			sprintf(
				'You need to specify static `id` parameter for %s section',
				$sectionClass,
			),
		);

		Assert::isArray(
			$section->controls(),
			/* translators: 1 - customizer section class name, 2 - customizer section controls type. */
			sprintf(
				'`controls()` method should return array for %1$s section, %2$s given',
				$sectionClass,
				gettype( $section->controls() ),
			),
		);
	}

	/**
	 * Assert section options are OK
	 *
	 * @param object $option | option object.
	 * @param string $optionClass | option class name.
	 * @throws \Exception | Assert this is a valid option.
	 */
	private function assertOption( object $option, string $optionClass ) {
		Assert::isArray(
			$option->settings(),
			/* translators: 1: customizer option class name; 2: customizer option settings type. */
			sprintf(
				'`settings()` method should return array for %1$s option, %2$s given',
				$optionClass,
				gettype( $option->settings()['args'] ),
			),
		);

		Assert::keyExists(
			$option->settings()['args'],
			'section',
			/* translators: 1: customizer section class name. */
			sprintf(
				'You need to specify `section` setting for %s option',
				$optionClass,
			),
		);
	}
}
