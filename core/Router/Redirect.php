<?php
/**
 * Redirect handler
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Router;

class Redirect
{

	/**
	 * Redirect to custom url.
	 *
	 * @param string $to | url.
	 * @return void
	 */
	public function to( string $to ) {
		wp_safe_redirect( esc_url( $to ) );
		exit;
	}

	/**
	 * Redirect home
	 *
	 * @return void
	 */
	public function home() {
		$homeUrl = config( 'auth.home' ) ?? esc_url( home_url( '/' ) );
		wp_safe_redirect( $homeUrl );
		exit;
	}

	/**
	 * Redirect back
	 *
	 * @return void
	 */
	public function back() {
		wp_safe_redirect( esc_url( wp_get_referer() ) );
		exit;
	}
}
