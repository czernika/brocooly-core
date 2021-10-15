<?php
/**
 * Allow extra files to be loaded into Media Library
 *
 * @package Brocooly
 * @since 0.16.2
 */

declare(strict_types=1);

namespace Brocooly\Hooks;

class AddMimeTypes
{

	public function filter() {
		return 'upload_mimes';
	}

	public function hook( $mime_types ) {
		$allowed    = config( 'uploads.allowed' );
		$disallowed = config( 'uploads.disallowed' );

		if ( ! empty( $allowed ) ) {
			foreach ( $allowed as $ext => $mime ) {
				$mime_types[ $ext ] = $mime;
			}
		}
		
		if ( ! empty( $disallowed ) ) {
			foreach ( $disallowed as $ext) {
				unset( $mime_types[ $ext ] );
			}
		}
		
		return $mime_types;
	}
}

