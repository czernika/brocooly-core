<?php
/**
 * Query builder.
 * Wrapper fow WP_Query
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Builders;

use Timber\Timber;
use Timber\PostQuery;

class QueryBuilder
{

	/**
	 * Default query parameters
	 *
	 * @var array
	 */
	protected array $queryParams = [
		'merge_default'  => true,
		'no_found_rows'  => true,
	];

	/**
	 * Get posts with pagination
	 *
	 * @param int|null $postsPerPage | post type name.
	 *
	 * @return array|null
	 */
	public function paginate( $postsPerPage = null ) {
		$postQuery           = [
			'posts_per_page' => $postsPerPage ?? (int) get_option( 'posts_per_page' ),
			'paged'          => max( 1, get_query_var( 'paged' ) ),
			'no_found_rows'  => false,
		];
		$this->queryParams = array_merge( $this->queryParams, $postQuery );

		return $this;
	}

	/**
	 * Merge current post types with any
	 * FIXME: post types added to main will inherit primary model
	 * Here Post::with( Page::POST_TYPE )->get() Page model will actually inherit Post model
	 * Which is fine, as this type of query requires both equal properties to have but at the same time it is not OK
	 *
	 * @param string|array $postTypes | additional post types
	 * @return $this
	 */
	public function with( $postTypes ) {
		$currentPostType = (array) $this->queryParams['post_type'];
		$queryPostTypes = array_merge( $currentPostType, (array) $postTypes );

		$postTypesQuery = [
			'post_type' => $queryPostTypes,
		];
		$this->queryParams = array_merge( $this->queryParams, $postTypesQuery );

		return $this;
	}

	/**
	 * Set query parameter
	 *
	 * @param string $key | query key.
	 * @param mixed  $value | query key value.
	 * @return self
	 */
	public function where( string $key, $value ) {
		$postQuery         = [ $key => $value ];
		$this->queryParams = array_merge( $this->queryParams, $postQuery );

		return $this;
	}

	/**
	 * Meta query builder
	 *
	 * @param string $key | meta key.
	 * @param mixed  $value | meta value.
	 * @param string $compare_key | compare key.
	 * @param string $compare | compare value.
	 * @param string $type | meta type.
	 * @return self
	 */
	public function whereMeta( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR' ) {
		return $this->metaBuilder( $key, $value, $compare_key, $compare, $type );
	}

	/**
	 * Meta query builder for OR relationship
	 *
	 * @param string $key | meta key.
	 * @param mixed  $value | meta value.
	 * @param string $compare_key | compare key.
	 * @param string $compare | compare value.
	 * @param string $type | meta type.
	 * @return self
	 */
	public function orWhereMeta( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR' ) {
		return $this->metaBuilder( $key, $value, $compare_key, $compare, $type, 'OR' );
	}

	/**
	 * Meta query builder for AND relationship
	 *
	 * @param string $key | meta key.
	 * @param mixed  $value | meta value.
	 * @param string $compare_key | compare key.
	 * @param string $compare | compare value.
	 * @param string $type | meta type.
	 * @return self
	 */
	public function andWhereMeta( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR' ) {
		return $this->metaBuilder( $key, $value, $compare_key, $compare, $type, 'AND' );
	}

	private function metaBuilder( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR', $relation = null ) {
		if ( is_array( $value ) ) {
			$compare = 'IN';
		}

		$metaQuery = [
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'         => $key,
					'value'       => $value,
					'compare_key' => $compare_key,
					'compare'     => $compare,
					'type'        => $type,
				],
			],
		];

		if ( $relation ) {
			$metaQuery['meta_query']['relation'] = $relation;
		}

		$this->queryParams = array_merge_recursive( $this->queryParams, $metaQuery );

		return $this;
	}

	/**
	 * Pass extra params to a query if condition is true
	 *
	 * @param $condition
	 * @param $callback
	 * @return $this
	 */
	public function when( $condition, $callback ) {
		if ( $condition ) {
			$query = call_user_func_array( $callback, [ $this ] );
			$this->queryParams = array_merge( $this->queryParams, $query->queryParams );
		}

		return $this;
	}

	/**
	 * Author query
	 *
	 * @param integer|string|array $authorId | array of authors id.
	 * @return self
	 */
	public function whereAuthor( $authorId ) {
		if ( is_array( $authorId ) ) {
			$authorQuery = [ 'author__in' => $authorId ];
		} elseif ( is_string( $authorId ) ) {
			$authorQuery = [ 'author_name' => $authorId ];
		} else {
			$authorQuery = [ 'author' => $authorId ];
		}

		$this->queryParams = array_merge( $this->queryParams, $authorQuery );

		return $this;
	}

	/**
	 * Set post status to query
	 *
	 * @param string|array $status | post status.
	 * @return self
	 */
	public function whereStatus( $status ) {
		$sortQuery           = [ 'post_status' => $status ];
		$this->queryParams = array_merge( $this->queryParams, $sortQuery );

		return $this;
	}

	/**
	 * Sort query
	 *
	 * @param string $order | order type.
	 * @param string $orderby | order by.
	 * @return self
	 */
	public function sort( string $order, string $orderby ) {
		$sortQuery         = [
			'order'   => $order,
			'orderby' => $orderby,
		];
		$this->queryParams = array_merge( $this->queryParams, $sortQuery );

		return $this;
	}

	/**
	 * Sort posts by ASC
	 *
	 * @param string $orderby | orderby key.
	 * @return self
	 */
	public function sortByAsc( string $orderby = 'ID' ) {
		$sortQuery         = [ 'order' => 'ASC', 'orderby' => $orderby ];
		$this->queryParams = array_merge( $this->queryParams, $sortQuery );

		return $this;
	}

	/**
	 * Sort posts by DESC
	 *
	 * @param string $orderby | orderby key.
	 * @return self
	 */
	public function sortByDesc( string $orderby = 'ID' ) {
		$sortQuery         = [ 'order' => 'ASC', 'orderby' => $orderby ];
		$this->queryParams = array_merge( $this->queryParams, $sortQuery );

		return $this;
	}

	/**
	 * Set offset to query
	 *
	 * @param integer $offset | number of posts to offset.
	 * @return self
	 */
	public function offset( int $offset ) {
		$offsetQuery         = [ 'offset' => $offset ];
		$this->queryParams = array_merge( $this->queryParams, $offsetQuery );

		return $this;
	}

	/**
	 * Ignore sticky posts
	 *
	 * @return self
	 */
	public function noSticky() {
		$noStickyQuery       = [ 'ignore_sticky_posts' => true ];
		$this->queryParams = array_merge( $this->queryParams, $noStickyQuery );

		return $this;
	}

	/**
	 * Suppress filter
	 *
	 * @return self
	 */
	public function suppress() {
		$suppressQuery       = [ 'suppress_filters' => true ];
		$this->queryParams = array_merge( $this->queryParams, $suppressQuery );

		return $this;
	}

	/**
	 * Date query AFTER date
	 *
	 * @param string $date | date after.
	 * @return self
	 */
	public function after( string $date ) {
		$dateQuery           = [
			'date_query' => [
				'after' => $date,
			],
		];
		$this->queryParams = array_merge( $this->queryParams, $dateQuery );

		return $this;
	}

	/**
	 * Date query BEFORE date
	 *
	 * @param string $date | date before.
	 * @return self
	 */
	public function before( string $date ) {
		$dateQuery           = [
			'date_query' => [
				'before' => $date,
			],
		];
		$this->queryParams = array_merge( $this->queryParams, $dateQuery );

		return $this;
	}

	/**
	 * Date query BETWEEN date
	 *
	 * @param string $before | date before.
	 * @param string $after | date after.
	 * @return self
	 */
	public function between( string $before, string $after ) {
		$dateQuery           = [
			'date_query' => [
				[
					'before' => $before,
				],
				[
					'after' => $after,
				],
			],
		];
		$this->queryParams = array_merge( $this->queryParams, $dateQuery );

		return $this;
	}

	/**
	 * Find posts by search phrase.
	 *
	 * @param string  $key | search phrase.
	 * @param boolean $exact | exact or not.
	 * @param boolean $sentence | consider full key phrase or not.
	 * @deprecated since Brocooly 0.16.4
	 * @return void
	 */
	public function findByPhrase( string $key, bool $exact = false, bool $sentence = false ) {
		$searchQuery        = [
			's'        => $key,
			'exact'    => $exact,
			'sentence' => $sentence,
		];
		$this->queryParams = array_merge( $this->queryParams, $searchQuery );

		return $this;
	}

	/**
	 * Find posts by search phrase.
	 * Same as findByPhrase()
	 *
	 * @param string  $key | search phrase.
	 * @param boolean $exact | exact or not.
	 * @param boolean $sentence | consider full key phrase or not.
	 * @return void
	 */
	public function search( string $key, bool $exact = false, bool $sentence = false ) {
		$searchQuery        = [
			's'        => $key,
			'exact'    => $exact,
			'sentence' => $sentence,
		];
		$this->queryParams = array_merge( $this->queryParams, $searchQuery );

		return $this;
	}

	/**
	 * Get posts by custom query
	 *
	 * @param string $name | post type name.
	 * @param array  $query | query array.
	 * @return array|null
	 */
	public function query( array $query ) {
		$postQuery = array_merge( $this->queryParams, $query );
		$posts     = $this->getQuery( $postQuery );

		return $posts;
	}

	/**
	 * Get query depends on version
	 *
	 * @param array $query | query.
	 * @return array|null
	 */
	protected function getQuery( array $query ) {
		if ( isTimberNext() ) {
			return Timber::get_posts( $query );
		}

		return new PostQuery( $query, get_class( app( $this->postType ) ) );
	}

	/**
	 * Get posts by query
	 *
	 * @return object
	 */
	public function get() {
		if ( isTimberNext() ) {
			return Timber::get_posts( $this->queryParams );
		}

		return new PostQuery( $this->queryParams, get_class( app( $this->postType ) ) );
	}

	/**
	 * Get posts collection
	 *
	 * @return object
	 */
	public function collect() {
		return collect( $this->get() );
	}

	/**
	 * Get first post of a collection
	 *
	 * @return object
	 */
	public function first() {
		$post = $this->collect()->first();
		return $post;
	}

	/**
	 * Get last post of a collection
	 *
	 * @return object
	 */
	public function last() {
		$post = $this->collect()->last();
		return $post;
	}

	/**
	 * Shuffle collection
	 *
	 * @return object
	 */
	public function shuffle() {
		$posts = $this->collect()->shuffle();
		return $posts;
	}

	/**
	 * Get random post of a collection
	 *
	 * @return object
	 */
	public function random() {
		$post = $this->collect()->random();
		return $post;
	}

	/**
	 * Custom scopes
	 *
	 * @param string $method | model method name
	 * @param array  $args | model method args
	 */
	public function callable( string $method, array $args ) {
		$postType = app( $this->queryParams['post_type'] );
		$query = call_user_func_array( [ $postType, $method ], [ $this, ...$args ] );
		return $query;
	}
}
