<?php
/**
 * Dispatch callable for router
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Router;

use Illuminate\Support\Str;
use Brocooly\Http\Controllers\BaseController;

class DispatchCallable
{
	public static function dispatch( $callable, $middleware ) {

		foreach ( (array) $middleware as $m ) {
			app( $m )->handle();
		}

		if ( is_array( $callable ) ) {
			// This is controller method.
			return static::dispatchControllerMethod( $callable );
		}

		if ( is_subclass_of( $callable, BaseController::class ) ) {
			// Call invokable class.
			$class = static::callController( $callable );
			return call_user_func_array( $class, func_get_args() );
		}

		/**
		 * @since Brocooly 0.13.1
		 */
		if ( is_string( $callable ) && Str::contains( $callable, '@' ) ) {
			$callable = explode( '@', $callable );
			return static::dispatchControllerMethod( $callable );
		}

		$funcArgs = static::getReflectedParams( $callable );
		return call_user_func_array( $callable, $funcArgs );
	}

	/**
	 * Bind objects directly into callbacks
	 *
	 * @param $callable
	 * @return array
	 */
	private static function getReflectedParams( $callable ) {
		if ( is_callable( $callable ) && ! is_array( $callable ) ) {
			$reflectedCallback = new \ReflectionFunction( $callable );

		} elseif ( is_array( $callable ) ) {
			[ $object, $method ] = $callable;
			$reflectedCallback = new \ReflectionMethod( $object, $method );
		}

		$args = static::getReflectedArgs( $reflectedCallback, $callable );
		return $args;
	}

	public static function getReflectedArgs( $reflectedCallback, $callable ) {
		$args = [];
		if ( $reflectedCallback->getNumberOfParameters() ) {
			$param = new \ReflectionParameter( $callable, 0 );
			$reflectedParamObject = $param->getType()->getName();
			$args = [ new $reflectedParamObject() ];
		}

		return $args;
	}

	/**
	 * Call method from Controller class.
	 *
	 * @param array $callback
	 * @return void
	 */
	private static function dispatchControllerMethod( array $callback ) {

		if ( count( $callback ) > 2 ) {
			[ $object, $method, $params ] = $callback;
		} else {
			$params = [];
			[ $object, $method ] = $callback;
		}

		$class = static::callController( $object );

		if ( method_exists( $class, 'middleware' ) ) {
			$middleware = $class->getMiddleware();

			foreach ( $middleware as $name => $m ) {

				if ( empty( $m['only'] ) && empty( $m['except'] ) ) {
					self::callMiddleware( $name );
				} else {

					if ( in_array( $method, $m['only'], true ) ) {
						self::callMiddleware( $name );
					} else {
						if ( empty( $m['only'] ) && ! in_array( $method, $m['except'], true ) ) {
							self::callMiddleware( $name );
						}
					}

				}

			}
		}

		$params = static::getReflectedParams( [ $class, $method ] );
		return call_user_func_array( [ $class, $method ], $params );
	}

	private static function callMiddleware( $name ) {
		app()->make( $name )->handle();
	}

	/**
	 * Inject all dependencies into Controller
	 *
	 * @param string $instance | class instance name.
	 * @return object
	 */
	private static function callController( string $instance ) {
		$class = app()->make( $instance );
		return $class;
	}
}

