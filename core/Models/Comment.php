<?php
/**
 * Abstract comment model
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Models;

use Carbon_Fields\Container;

class Comment
{

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
			return carbon_get_comment_meta( $this->id, $field );
		}
	}

	/**
	 * Create metabox container for post types
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
		$container = Container::make( 'comment_meta', $id, $label );
		return $container;
	}

}
