<?php

declare(strict_types=1);

namespace Brocooly\Http\Controllers;

class MiddlewareController
{
	private $middleware;

	public function __construct( $middleware ) {
		$this->initMiddleware( $middleware );
	}

	public function initMiddleware( $middleware ) {
		foreach ( (array) $middleware as $m ) {
			$this->middleware[ $m ] = [
				'handler' => app( $m ),
				'only'    => [],
				'except'  => [],
			];
		}
	}

	public function setMiddleware( $key, $value ) {
		foreach ( $this->middleware as $name => $m ) {
			$this->middleware[ $name ][ $key ] = $value;
		}
	}

	public function getMiddleware() {
		return $this->middleware;
	}
}
