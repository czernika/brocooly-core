<?php
/**
 * Handle Requests
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Http\Request;

use Brocooly\Support\Facades\Validator;

class Request
{

	/**
	 * First error to show
	 *
	 * @var string
	 */
	protected string $firstError;

	/**
	 * Return validation rules array
	 *
	 * @return array
	 * @throws Exception
	 */
	protected function rules() {
		throw new \Exception( 'You need to specify validation rules' );
	}

	/**
	 * Validate request
	 *
	 * @param array $data | data to validate.
	 * @param array|null $rules | validation rules.
	 * @return object
	 */
	public function validate( array $data, $rules = null ) {
		if ( ! isset( $rules ) ) {
			$rules = $this->rules();
		}
		return Validator::make( $data, $rules );
	}

	/**
	 * Return first error message
	 *
	 * @return string
	 */
	public function getFirstErrorMessage() {
		return $this->firstError;
	}
}
