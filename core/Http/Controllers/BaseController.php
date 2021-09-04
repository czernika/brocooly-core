<?php
/**
 * BaseController instance
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Http\Controllers;

use Brocooly\App;

abstract class BaseController
{

	/**
	 * Application instance
	 *
	 * @var instanceof Brocooly\App
	 */
	protected $app;

	protected array $middleware = [];

	protected array $only = [];

	public function __construct( App $app ) {
		$this->app = $app;
	}

	public function getOnlyMethods() {
		return $this->only;
	}

	public function getMiddleware() {
		return $this->middleware;
	}

	public function middlewareOnly( $methods, $middleware ) {
		$this->only = (array) $methods;
		$this->addMiddleware( $middleware );
	}

	public function middleware( $middleware ) {
		$this->addMiddleware( $middleware );
	}

	public function loadMiddleware() {
		foreach ( $this->middleware as $middleware ) {
			app()->call( [ $middleware, 'handle' ] );
		}
	}

	private function addMiddleware( $middleware ) {
		if ( is_string( $middleware ) ) {
			$this->middleware[] = $middleware;
		}

		if ( is_array( $middleware ) ) {
			$this->middleware = array_merge( $this->middleware, $middleware );
		}
	}

}
