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
use Webmozart\Assert\Assert;

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
			if ( call_user_func( ...$route['name'] ) ) {
				DispatchCallable::dispatch( $route['callback'] );
				return true;
			}
		}
		return false;
	}

	public static function handleAjaxRequest() {
		$routes = Routes::getRoutes();
		if ( Arr::exists( $routes, 'ajax' ) ) {
			$ajaxRoutes = $routes['ajax'];
			foreach ( $ajaxRoutes as $route ) {
				[ $action, $callable ] = $this->dispatchCallback( $route );

				add_action( "wp_ajax_$action", $callable );
				add_action( "wp_ajax_nopriv_$action", $callable );
			}
		}
	}

	public static function handlePostRequest() {
		$routes = Routes::getRoutes();
		if ( Arr::exists( $routes, 'post' ) ) {
			$postRoutes = $routes['post'];

			foreach ( $postRoutes as $route ) {
				[ $action, $callable ] = $this->dispatchCallback( $route );

				add_action( "admin_post_$action", $callable );
				add_action( "admin_post_nopriv_$action", $callable );
			}
		}
	}

	private function dispatchCallback( $route ) {
		$action   = $route['name'][0];
		$callback = $route['callback'];

		if ( is_array( $callback ) ) {
			[ $callerClass, $method ] = $callback;
			$classObject = app( $callerClass );
			$callable = [ $classObject, $method ];
		} else {
			$callable = $callback ;
		}

		return [ $action, $callable ];
	}
}
