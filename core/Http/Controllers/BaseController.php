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

	private $middleware;

	public function __construct( App $app ) {
		$this->app = $app;
	}

	protected function only( $methods ) {
		$this->middleware->setMiddleware( 'only', (array) $methods );
	}

	protected function except( $methods ) {
		$this->middleware->setMiddleware( 'except', (array) $methods );
	}

	public function middleware( $middleware ) {
		$this->middleware = new MiddlewareController( $middleware );
		return $this;
	}

	public function getMiddleware() {
		if ( $this->middleware ) {
			return $this->middleware->getMiddleware();
		}

		return [];
	}
}
