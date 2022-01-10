<?php
/**
 * Register custom Gutenberg blocks
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Brocooly\App;
use Webmozart\Assert\Assert;

class BlockServiceProvider extends AbstractService
{

	private bool $use_editor;

	private bool $deregister;

	private array $blocks;

	public function __construct( App $app ) {
		$this->use_editor = config( 'blocks.use_editor', false );
		$this->blocks     = config( 'blocks.blocks', [] );
		$this->deregister = config( 'blocks.deregister_block_styles', false );

		parent::__construct( $app );
	}

	public function boot() {
		if( ! $this->use_editor ){
			$this->disableEditor();
			return;
		}

		$this->registerBlocks();
	}

	/**
	 * Register blocks
	 */
	private function registerBlocks() {
		Assert::isArray( $this->blocks, 'Blocks should be an array' );

		foreach ( $this->blocks as $block ) {
			$blockClass = $this->app->make( $block );

			if ( method_exists( $blockClass, 'render' ) ) {
				add_action(
					'carbon_fields_register_fields',
					[ $blockClass, 'render' ],
				);
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

		if ( $this->deregister ) {
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

