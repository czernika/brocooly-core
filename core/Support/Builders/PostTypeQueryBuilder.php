<?php
/**
 * WP_Query wrapper for Post Type
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Builders;

use Timber\Post;
use Timber\Timber;

class PostTypeQueryBuilder extends QueryBuilder
{

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	protected string $postType;

	/**
	 * Set post_type query as post type slug
	 *
	 * @param string $postType
	 */
	public function __construct( string $postType ) {
		$this->postType                      = $postType;
		$this->queryParams['post_type']      = $this->postType;
		$this->queryParams['posts_per_page'] = config( 'views.limit' );
	}

	/**
	 * Get all posts
	 *
	 * @return array|null
	 */
	public function all() {
		$postQuery = [
			'posts_per_page' => config( 'views.limit', 500 ),
			'no_found_rows'  => true,
		];
		$query     = array_merge( $this->queryParams, $postQuery );
		$posts     = $this->getQuery( $query );

		return $posts;
	}

	/**
	 * Get post by id
	 *
	 * @param integer $id | post id.
	 * @return \Timber\Post object
	 */
	public function find( int $id ) {
		if ( isTimberNext() ) {
			return Timber::get_post( $id );
		}

		return app()->make( $this->postType, compact( 'id' ) );
	}

	/**
	 * Get current post object
	 *
	 * @return object
	 */
	public function current() {
		if ( isTimberNext() ) {
			return Timber::get_post();
		}

		return app( $this->postType );
	}

	/**
	 * Custom scopes
	 *
	 * @param string $method | model method name
	 * @param array  $args | model method args
	 */
	public function callable( string $method, array $args ) {
		$postType = app( $this->postType );
		$query = call_user_func_array( [ $postType, $method ], [ $this, ...$args ] );
		return $query;
	}
}
