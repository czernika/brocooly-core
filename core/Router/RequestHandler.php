<?php
/**
 * Handle requests.
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Router;

use Illuminate\Support\Arr;

class RequestHandler
{

	private static $routes = [];

	public static function defineRoute( $routes ) {
		self::$routes = $routes;

		$routeWasDefined = self::handleGetRequest();
		return $routeWasDefined;
	}

	private static function handleGetRequest() {
		$routes = collect( self::$routes )
					->except( [ 'post', 'ajax' ] )
					->all();

		foreach ( $routes['get'] as $route ) {
			if ( call_user_func( ...$route['condition'] ) ) {
				DispatchCallable::dispatch( $route['callback'], $route['middleware'] );
				return true;
			}
		}
		return false;
	}

	public static function handleAjaxRequest() {
		self::handleRequest( 'ajax', 'wp_ajax' );
	}

	public static function handlePostRequest() {
		self::handleRequest( 'post', 'admin_post' );
	}

	private static function handleRequest( $type, $actionName ) {
		$routes = app( Router::class )->getRoutes();

		if ( Arr::exists( $routes, $type ) ) {
			$ajaxRoutes = $routes[ $type ];
			foreach ( $ajaxRoutes as $route ) {
				[ $action, $callable, $params ] = self::dispatchCallback( $route );

				add_action( "${actionName}_${action}", function() use ( $callable, $params ) {
					return call_user_func_array( $callable, $params );
				} );

				if ( $route['nopriv'] ) {
					add_action( "${actionName}_nopriv_${action}", function() use ( $callable, $params ) {
						return call_user_func_array( $callable, $params );
					} );
				}
			}
		}
	}

	private static function dispatchCallback( $route ) {
		$action   = $route['condition'][0];
		$callback = $route['callback'];
		$params   = [];

		if ( is_array( $callback ) ) {

			if ( count( $callback ) > 2 ) {
				[ $callerClass, $method, $params ] = $callback;
			} else {
				[ $callerClass, $method ] = $callback;
			}

			$classObject = app( $callerClass );
			$callable = [ $classObject, $method ];

		} else {
			$callable = $callback ;
		}

		return [ $action, $callable, $params ];
	}
}
