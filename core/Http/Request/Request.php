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
	
	private array $attachments = [];

	public function __get( $key ) {
		return $this->$key;
	}

	public function __set( $key, $value ) {
		$this->$key = $value;
	}

	public function handleUploads( $data, $test = false ) {
		if ( $data ) {
			foreach ( $data as $name => $file ) {

				$fileTmpName = (array) $file['tmp_name'];
				$fileName    = (array) $file['name'];
				$fileType    = (array) $file['type'];
				$fileError   = (array) $file['error'];
				$fileSize    = (array) $file['size'];

				for ( $i = 0; $i < count( $fileName ); $i++ ) {
					if ( ! $fileSize[ $i ] ) {
						continue;
					}

					$this->attachments[ $name ][ $i ] = new UploadedFile(
						$fileTmpName[ $i ],
						$fileName[ $i ],
						$fileType[ $i ],
						$fileError[ $i ],
						$test,
					);

					$this->$name = $this->attachments[ $name ][ $i ];
				}
			}
		}
	}

	public function file( $key = null ) {
		if ( $_FILES ) {
			if ( ! array_key_exists( $key, $this->attachments ) && $key ) {
				return null;
			}

			return $key ? $this->attachments[ $key ] : $this->attachments;
		}

		return null;
	}

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
	
	public function __call( $name, $args ) {
		return $this->validate( ...$args )->$name();
	}
	
	public function validated( array $data, $rules = null ) {
		if ( ! isset( $rules ) ) {
			$rules = $this->rules();
		}
		return $this->validate()->validated();
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
