<?php
/**
 * Abstract post type model
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Models;

use Timber\Post;
use Timber\PostQuery;
use Illuminate\Support\Str;
use Carbon_Fields\Container;
use Brocooly\Contracts\ModelContract;
use Brocooly\Support\Builders\PostTypeQueryBuilder;

/**
 * @method static PostQuery all()
 * @method static Post find( int $id )
 * @method static $this pluck( $key = 'ids' )
 * @method static Post current()
 * @method static $this paginate( $postsPerPage = null )
 * @method static $this with( $postTypes )
 * @method static $this where( string $key, $value )
 * @method static $this whereMeta( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR' )
 * @method static $this orWhereMeta( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR' )
 * @method static $this andWhereMeta( string $key, $value, string $compare_key = '=', string $compare = '=', string $type = 'CHAR' )
 * @method static $this when( $condition, $callback )
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
abstract class PostType extends Post implements ModelContract
{

	/**
	 * Register post type or not
	 * Sometimes you don't want to register post type as it is already may be registered
	 * but you want to add extra metaboxes or queries
	 *
	 * In that case just set this variable to `true`
	 *
	 * @var bool
	 */
	public bool $doNotRegister = false;

	/**
	 * Get array of protected meta calling as methods
	 *
	 * @var array
	 */
	protected array $allowedMeta = [];

	/**
	 * Post type slug
	 *
	 * @var string
	 */
	const POST_TYPE = 'post';

	/**
	 * Get carbon meta or parent function
	 *
	 * @param string $field | method name.
	 * @param array $args | function arguments.
	 * @return mixed
	 */
	public function __call( $field, $args ) {
		if ( in_array( $field, $this->allowedMeta, true ) ) {
			return carbon_get_post_meta( $this->id, $field );
		}

		return parent::__call( $field, $args );
	}

	/**
	 * Get post type name
	 *
	 * @return string
	 */
	public function getName() {
		if ( ! static::POST_TYPE ) {
			throw new \Exception( 'You must specify post type name', true );
		}

		return static::POST_TYPE;
	}

	/**
	 * Set post type options
	 *
	 * @throws Exception
	 */
	protected function options() {
		throw new \Exception( 'You must specify post type options', true );
	}

	/**
	 * Return post type options
	 *
	 * @throws Exception
	 */
	public function getOptions() {
		return $this->options();
	}

	/**
	 * Create metabox container for post types
	 *
	 * @param string $id | container id.
	 * @param string $label | container label.
	 * @param array  $fields | array of custom fields.
	 * @param string $context | The part of the page where the container should be shown.
	 * @return void
	 */
	protected function createFields( string $id, string $label, array $fields, string $context = 'normal' ) {
		$this->setContainer( $id, $label )
			->add_fields( $fields )
			->set_context( $context );
	}

	/**
	 * Set metabox container
	 *
	 * @param string $id | container id.
	 * @param string $label | container label.
	 * @return object
	 */
	protected function setContainer( string $id, string $label ) {
		$container = Container::make( 'post_meta', $id, $label )
						->where( 'post_type', '=', static::POST_TYPE );
		return $container;
	}

	/**
	 * Get posts by query
	 *
	 * @param string $name | method name.
	 * @param array  $arguments | method options.
	 * @return void
	 */
	public static function __callStatic( string $name, array $arguments ) {

		$builder = app()->make(
			PostTypeQueryBuilder::class,
			[ 'postType' => static::POST_TYPE ],
		);

		if ( ! method_exists( PostTypeQueryBuilder::class, $name ) ) {
			$method = 'scope' . Str::ucfirst( $name );
			return $builder->callable( $method, $arguments );
		}

		return $builder->$name( ...$arguments );
	}

	/**
	 * Create post type draft
	 *
	 * @param array   $data | passed data.
	 * @param boolean $wp_error | show error as WP_Error object.
	 * @return int | post id.
	 */
	public static function createDraft( array $data, bool $wp_error = false ) {
		$data['post_type']   = static::POST_TYPE;
		return wp_insert_post( wp_slash( $data ), $wp_error );
	}

	/**
	 * Create post type
	 *
	 * @param array   $data | passed data.
	 * @param boolean $wp_error | show error as WP_Error object.
	 * @return int | post id.
	 */
	public static function create( array $data, bool $wp_error = false ) {
		$data['post_status'] = 'publish';
		$data['post_type']   = static::POST_TYPE;
		return wp_insert_post( wp_slash( $data ), $wp_error );
	}

}
