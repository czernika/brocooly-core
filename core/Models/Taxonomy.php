<?php
/**
 * Abstract taxonomy model
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Models;

use Timber\Term;
use Timber\PostQuery;
use Carbon_Fields\Container;
use Brocooly\Support\Builders\TaxonomyQueryBuilder;

/**
 * @method static array all( array $args = [] )
 * @method static $this paginateArchive( int $postsPerPage = 10 )
 * @method static $this wherePostType( $postTypes = 'post' )
 * @method static $this whereTerm( string $key, $value, $operator = 'IN' )
 * @method static $this andWhereTerm( string $key, $value, string $operator = 'IN' )
 * @method static $this orWhereTerm( string $key, $value, string $operator = 'IN' )
 * @method static $this paginate( $postsPerPage = null )
 * @method static $this where( string $key, $value )
 * @method static $this whereMeta( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR' )
 * @method static $this orWhereMeta( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR' )
 * @method static $this andWhereMeta( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR' )
 * @method static $this whereAuthor( $authorId )
 * @method static $this whereStatus( $status )
 * @method static $this sort( string $order, string $orderby )
 * @method static $this sortByAsc( string $orderby = 'ID' )
 * @method static $this sortByDesc( string $orderby = 'ID' )
 * @method static $this offset( int $offset )
 * @method static $this noSticky()
 * @method static $this suppress()
 * @method static $this after( string $date )
 * @method static $this before( string $date )
 * @method static $this between( string $before, string $after )
 * @method static $this findByPhrase( string $key, bool $exact = false, bool $sentence = false )
 * @method static $this search( string $key, bool $exact = false, bool $sentence = false )
 * @method static PostQuery query( array $query )
 * @method static PostQuery get()
 * @method static Collection collect()
 * @method static Collection shuffle()
 * @method static Post first()
 * @method static Post last()
 * @method static Post random()
 */
abstract class Taxonomy extends Term
{

	/**
	 * Register taxonomy or not
	 * Sometimes you don't want to register taxonomy as it is already may be registered
	 * but you want to add extra metaboxes or queries
	 *
	 * In that case just set this variable to `true`
	 *
	 * @var bool
	 */
	public bool $doNotRegister = false;

	/**
	 * Taxonomy slug
	 *
	 * @var string
	 */
	const TAXONOMY = 'category';

	/**
	 * Post type related to taxonomy
	 * Same as for `register_taxonomy`
	 *
	 * @var array|string
	 */
	protected static $postTypes = 'post';

	/**
	 * Get array of protected meta calling as methods
	 *
	 * @var array
	 */
	protected array $allowedMeta = [];

	/**
	 * Get carbon meta or parent function
	 *
	 * @param string $field | method name.
	 * @param array $args | function arguments.
	 * @return mixed
	 */
	public function __call( $field, $args ) {
		if ( in_array( $field, $this->allowedMeta, true ) ) {
			return carbon_get_term_meta( $this->id, $field );
		}

		return parent::__call( $field, $args );
	}

	/**
	 * Get post type name
	 *
	 * @return string
	 * @throws \Exception | taxonomy name was not set.
	 */
	public function getName() {
		if ( ! static::TAXONOMY ) {
			throw new \Exception( 'You must specify taxonomy name' );
		}

		return static::TAXONOMY;
	}

	/**
	 * Get taxonomy post types
	 *
	 * @return string|array
	 * @throws \Exception | Post type was not set.
	 */
	public function getPostTypes() {
		if ( ! static::$postTypes ) {
			throw new \Exception(
				/* translators: 1: taxonomy slug. */
				sprintf(
					'You must specify post type related to %s taxonomy',
					static::TAXONOMY
				)
			);
		}

		return static::$postTypes;
	}

	/**
	 * Set taxonomy options
	 *
	 * @throws \Exception | Taxonomy options were not set.
	 */
	protected function options() {
		throw new \Exception( 'You must specify taxonomy options' );
	}

	/**
	 * Return taxonomy options
	 *
	 * @return array
	 */
	public function getOptions() {
		return $this->options();
	}

	/**
	 * Create metabox container for taxonomy
	 *
	 * @param string $id | container id.
	 * @param string $label | container label.
	 * @param array  $fields | array of custom fields.
	 * @return void
	 */
	protected function createFields( string $id, string $label, array $fields ) {
		$this->setContainer( $id, $label )
			->add_fields( $fields );
	}

	/**
	 * Set metabox container
	 *
	 * @param string $id | container id.
	 * @param string $label | container label.
	 * @return object
	 */
	protected function setContainer( string $id, string $label ) {
		$container = Container::make( 'term_meta', $id, $label )
						->where( 'term_taxonomy', '=', static::TAXONOMY );
		return $container;
	}

	/**
	 * Get posts by query
	 *
	 * @param string $method | method name.
	 * @param array  $arguments | method options.
	 * @return void
	 */
	public static function __callStatic( string $name, array $arguments ) {
		return app()->make(
			TaxonomyQueryBuilder::class,
			[ 'postType' => static::$postTypes, 'taxonomy' => static::TAXONOMY ],
		)->$name( ...$arguments );
	}

	/**
	 * Create new term
	 *
	 * @param string $term | term name.
	 * @param array  $data | additional data, like parent, slug, description.
	 * @return array | term taxonomy data.
	 */
	public static function create( string $term, array $data ) {
		return wp_insert_term( $term, static::TAXONOMY, $data );
	}

}
