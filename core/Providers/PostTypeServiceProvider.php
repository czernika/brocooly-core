<?php
/**
 * Register custom post types and taxonomies
 * Register nav menus and add them into global context
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Webmozart\Assert\Assert;

use function DI\create;

class PostTypeServiceProvider extends AbstractService
{

	/**
	 * Values reserved by WordPress and WooCommerce
	 *
	 * @var array
	 */
	private array $protectedPostTypes = [
		'post',
		'page',
		'revision',
		'attachment',
		'nav_menu_item',
	];

	/**
	 * Values reserved by WordPress
	 *
	 * @var array
	 */
	private array $protectedTaxonomies = [ 'category', 'post_tag' ];

	public function register() {
		foreach ( config( 'app.post_types', [] ) as $postTypeClass ) {
			$postType = $this->app->get( $postTypeClass );
			if ( ! $this->app->has( $postType->getName() ) ) {
				$this->app->set( $postType->getName(), create( $postTypeClass ) );
			}
		}

		foreach ( config( 'app.taxonomies', [] ) as $taxonomyClass ) {
			$taxonomy = $this->app->get( $taxonomyClass );
			if ( ! $this->app->has( $taxonomy->getName() ) ) {
				$this->app->set( $taxonomy->getName(), create( $taxonomyClass ) );
			}
		}
	}

	public function boot() {
		$this->registerTaxonomies();
		$this->registerPostTypes();
		$this->registerComments();
	}

	/**
	 * Register post types
	 */
	private function registerPostTypes() {
		$postTypes = config( 'app.post_types', [] );

		if ( ! empty( $postTypes ) ) {
			foreach ( $postTypes as $postTypeClass ) {
				$cpt = $this->app->get( $postTypeClass );

				$this->callMetaFields( $cpt, 'fields' );
				$this->callMetaFields( $cpt, 'thumbnail' ); // thumbnail trait.

				if ( in_array( $cpt->getName(), $this->protectedPostTypes, true ) || $cpt->doNotRegister ) {
					continue;
				}

				$this->checkPostType( $cpt, $postTypeClass );

				add_action( 'init', function() use ( $cpt ) {
					register_extended_post_type( $cpt->getName(), $cpt->getOptions(), [ 'slug' => $cpt->webUrl ] );
				} );
			}
		}
	}

	private function callMetaFields( object $cpt, string $method ) {
		if ( method_exists( $cpt, $method ) ) {
			add_action(
				'carbon_fields_register_fields',
				[ $cpt, $method ],
			);
		}
	}

	private function checkPostType( $cpt, $postTypeClass ) {
		Assert::methodExists(
			$cpt,
			'options',
			/* translators: 1: post type class. */
			sprintf(
				'Method options was not set for %s taxonomy',
				$postTypeClass,
			),
		);
	}

	/**
	 * Register taxonomies
	 */
	private function registerTaxonomies() {
		$taxonomies = config( 'app.taxonomies' );

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomyClass ) {
				$tax = $this->app->get( $taxonomyClass );

				$this->callMetaFields( $tax, 'fields' );

				if ( in_array( $tax->getName(), $this->protectedTaxonomies, true ) || $tax->doNotRegister ) {
					continue;
				}

				$this->checkTax( $tax, $taxonomyClass );

				add_action( 'init', function() use ( $tax ) {
					register_extended_taxonomy( $tax->getName(), $tax->getPostTypes(), $tax->getOptions(), [ 'slug' => $tax->webUrl ] );
				} );
			}
		}
	}

	private function checkTax( $tax, $taxonomyClass ) {
		Assert::methodExists(
			$tax,
			'options',
			/* translators: 1: taxonomy class. */
			sprintf(
				'Method options was not set for %s taxonomy',
				$taxonomyClass,
			),
		);
	}

	/**
	 * Register comment container
	 */
	private function registerComments() {
		$commentClass = $this->app->get( 'comments.parent' );

		if ( $commentClass && class_exists( $commentClass ) ) {
			$comment = $this->app->get( $commentClass );
			if ( method_exists( $comment, 'fields' ) ) {
				add_action(
					'carbon_fields_register_fields',
					[ $comment, 'fields' ],
				);
			}
		}
	}
}


