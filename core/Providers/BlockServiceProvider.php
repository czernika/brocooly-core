<?php
/**
 * Register custom Gutenberg blocks
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Webmozart\Assert\Assert;

class BlockServiceProvider extends AbstractService
{

	public function register() {
		$this->app->set( 'custom_blocks', config( 'blocks.blocks', [] ) );
		$this->app->set( 'use_gutenberg', (bool) config( 'blocks.use_editor', true ) );
	}

	public function boot() {
		if( ! $this->app->get( 'use_gutenberg' ) ){
			$this->disableEditor();
			return;
		}

		$this->registerBlocks();
	}

	/**
	 * Register blocks
	 */
	private function registerBlocks() {
		$blocks = $this->app->get( 'custom_blocks' );

		Assert::isArray( $blocks, 'Blocks should be an array' );

		if ( ! empty( $blocks ) ) {
			foreach ( $blocks as $block ) {
				$blockClass = $this->app->make( $block );

				if ( method_exists( $blockClass, 'render' ) ) {
					add_action(
						'carbon_fields_register_fields',
						[ $blockClass, 'render' ],
					);
				}
			}
		}
	}

	/**
	 * Disable Gutenberg editor
	 *
	 * @since 1.1.0
	 * @since Brocooly 0.12.2
	 */
	private function disableEditor() {
		remove_theme_support( 'core-block-patterns' ); // WP 5.5

		add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );

		if ( (bool) config( 'blocks.deregister_block_styles', true ) ) {
			remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles' );
		}

		add_action(
			'admin_init',
			function() {
				remove_action( 'admin_notices', [ 'WP_Privacy_Policy_Content', 'notice' ] );
				add_action( 'edit_form_after_title', [ 'WP_Privacy_Policy_Content', 'notice' ] );
			}
		);
	}
}
