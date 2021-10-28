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
	public static function dispatch( $callable ) {
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

		return call_user_func_array( $callable, func_get_args() );
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

		$onlyMethods = $class->getOnlyMethods();
		if ( method_exists( $class, 'middlewareOnly' ) && ! empty( $onlyMethods ) ) {
			if ( in_array( $method, $onlyMethods, true ) ) {
				$class->loadMiddleware();
			}
		}

		if ( method_exists( $class, 'middleware' ) && empty( $onlyMethods ) ) {
			$class->loadMiddleware();
		}

		return call_user_func_array( [ $class, $method ], $params );
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
