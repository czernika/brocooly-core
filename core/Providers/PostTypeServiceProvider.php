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

				if ( in_array( $cpt->getName(), $this->protectedPostTypes, true ) || $cpt->doNotRegister ) {

					/**
					 * No need to register or check any post type options
					 * which is already registered (like post, page)
					 */
					continue;
				}

				$this->callMetaFields( $cpt, 'fields' );
				$this->callMetaFields( $cpt, 'thumbnail' ); // thumbnail trait.

				Assert::methodExists(
					$cpt,
					'options',
					/* translators: 1: post type class. */
					sprintf(
						'Method options was not set for %s taxonomy',
						$postTypeClass,
					),
				);

				add_action(
					'init',
					function() use ( $cpt ) {
						register_post_type(
							$cpt->getName(),
							$cpt->getOptions(),
						);
					}
				);
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

	/**
	 * Register taxonomies
	 */
	private function registerTaxonomies() {
		$taxonomies = config( 'app.taxonomies' );

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomyClass ) {

				$tax = $this->app->get( $taxonomyClass );

				if ( in_array( $tax->getName(), $this->protectedTaxonomies, true ) || $tax->doNotRegister ) {

					continue;
				}

				$this->callMetaFields( $tax, 'fields' );

				Assert::methodExists(
					$tax,
					'options',
					/* translators: 1: taxonomy class. */
					sprintf(
						'Method options was not set for %s taxonomy',
						$taxonomyClass,
					),
				);

				add_action(
					'init',
					function() use ( $tax ) {
						register_taxonomy(
							$tax->getName(),
							$tax->getPostTypes(),
							$tax->getOptions(),
						);
					}
				);
			}
		}
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

