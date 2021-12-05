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
				$action                   = $route['name'][0];
				[ $callerClass, $method ] = $route['callback'];
				$classObject = app( $callerClass );

				add_action( "wp_ajax_$action", [ $classObject, $method ] );
				add_action( "wp_ajax_nopriv_$action", [ $classObject, $method ] );
			}
		}
	}

	public static function handlePostRequest() {
		$routes = Routes::getRoutes();
		if ( Arr::exists( $routes, 'post' ) ) {
			$postRoutes = $routes['post'];

			foreach ( $postRoutes as $route ) {
				$action                   = $route['name'][0];
				[ $callerClass, $method ] = $route['callback'];
				$classObject = app( $callerClass );

				add_action( "admin_post_$action", [ $classObject, $method ] );
				add_action( "admin_post_nopriv_$action", [ $classObject, $method ] );
			}
		}
	}
}
