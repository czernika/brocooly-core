<?php

declare(strict_types=1);

namespace Brocooly\Storage;

class Option
{
	public function get( string $option, $default = false ) {
		return get_option( $option, $default );
	}

	public function add( string $option, $value = '', $deprecated = '', $autoload = 'yes' ) {
		return add_option( $option, $value, $deprecated, $autoload );
	}

	public function update( $option, $value, $autoload = 'yes' ) {
		return update_option( $option, $value, $autoload );
	}

	public function delete( $option ) {
		return delete_option( $option );
	}
}
