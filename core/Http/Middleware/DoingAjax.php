<?php
/**
 * Doing AJAX middleware
 * Check if current request is AJAX
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Http\Middleware;

class DoingAjax extends AbstractMiddleware
{
	public function handle() {
		if ( ! wp_doing_ajax() ) {
			return;
		}
	}
}
