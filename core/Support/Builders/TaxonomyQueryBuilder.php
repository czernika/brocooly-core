<?php
/**
 * Set taxonomy query
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Builders;

class TaxonomyQueryBuilder extends QueryBuilder
{

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	protected string $postType;

	/**
	 * Taxonomy slug
	 *
	 * @var string
	 */
	private string $taxonomy;

	/**
	 * Set taxonomy query
	 *
	 * @param string $postType
	 * @param string $taxonomy
	 */
	public function __construct( string $postType, string $taxonomy ) {
		$this->postType                      = $postType;
		$this->taxonomy                      = $taxonomy;
		$this->queryParams['post_type']      = $this->postType;
		$this->queryParams['posts_per_page'] = config( 'views.limit' );
	}

	/**
	 * Get all posts of current taxonomy
	 *
	 * @return void
	 */
	public function all() {
		return $this->whereTermId(
			get_terms(
				[
					'taxonomy' => $this->taxonomy,
					'fields'   => 'ids',
				]
			)
		);
	}
	/**
	 * Set posts per page for query.
	 * ! This function should NOT be called first
	 * Consider to use `where`
	 *
	 * @param integer $postsPerPage | posts per page.
	 * @return self
	 */
	public function paginateArchive( int $postsPerPage = 10 ) {
		$taxQuery            = [
			'posts_per_archive_page' => $postsPerPage,
			'paged'                  => max( 1, get_query_var( 'paged' ) ),
			'no_found_rows'          => false,
		];
		$this->queryParams = array_merge( $this->queryParams, $taxQuery );
		return $this;
	}

	/**
	 * Set post type for query.
	 * ! This function should NOT be called first
	 * Consider to use `where`
	 *
	 * @param string|array $postTypes | post types.
	 * @return self
	 */
	public function wherePostType( $postTypes = 'post' ) {
		$taxQuery            = [ 'post_type' => $postTypes ];
		$this->queryParams = array_merge( $this->queryParams, $taxQuery );
		return $this;
	}

	/**
	 * Set taxonomy query params
	 *
	 * @param string $key | field.
	 * @param mixed  $value | field value.
	 * @param string $operator | field operator.
	 * @return self
	 */
	public function whereTerm( string $key, $value, $operator = 'IN' ) {
		$taxQuery = [
			'tax_query' => [
				[
					'taxonomy' => $this->taxonomy,
					'field'    => $key,
					'terms'    => $value,
					'operator' => $operator,
				],
			],
		];

		$this->queryParams = array_merge( $this->queryParams, $taxQuery );
		return $this;
	}

	/**
	 * Set AND relation to a taxonomy query
	 *
	 * @param string $key | field.
	 * @param mixed  $value | field value.
	 * @param string $operator | field operator.
	 * @return self
	 */
	public function andWhereTerm( string $key, $value, string $operator = 'IN' ) {
		$taxQuery          = $this->setQuery( 'AND', $key, $value, $operator );
		$this->queryParams = array_merge( $this->queryParams, $taxQuery );
		return $this;
	}

	/**
	 * Set OR relation to a taxonomy query
	 *
	 * @param string $key | field.
	 * @param mixed  $value | field value.
	 * @param string $operator | field operator.
	 * @return self
	 */
	public function orWhereTerm( string $key, $value, string $operator = 'IN' ) {
		$taxQuery          = $this->setQuery( 'OR', $key, $value, $operator );
		$this->queryParams = array_merge( $this->queryParams, $taxQuery );
		return $this;
	}

	/**
	 * Shortcut for whereTerm method
	 *
	 * @param integer|array $id | term id
	 * @return void
	 */
	public function whereTermId( $id ) {
		return $this->whereTerm( 'id', $id );
	}

	/**
	 * Shortcut for whereTerm method
	 *
	 * @param integer $name | term name
	 * @return void
	 */
	public function whereTermName( string $name ) {
		return $this->whereTerm( 'name', $name );
	}

	/**
	 * Shortcut for whereTerm method
	 *
	 * @param integer $slug | term slug
	 * @return void
	 */
	public function whereTermSlug( string $slug ) {
		return $this->whereTerm( 'slug', $slug );
	}

	/**
	 * Shortcut for whereTerm method
	 * @return void
	 */
	public function currentTerm() {
		return $this->whereTerm( 'id', get_queried_object_id() );
	}

	/**
	 * Set query relation
	 *
	 * @param string $relation | taxonomy relation - OR or AND.
	 * @param string $key | field.
	 * @param mixed  $value | field value.
	 * @param string $operator | field operator.
	 * @return array
	 */
	private function setQuery( string $relation, string $key, $value, string $operator ) {
		$query = [
			'tax_query' => [
				'relation' => $relation,
				[
					'taxonomy' => $this->taxonomy,
					'field'    => $key,
					'terms'    => $value,
					'operator' => $operator,
				],
			],
		];
		return $query;
	}
}
