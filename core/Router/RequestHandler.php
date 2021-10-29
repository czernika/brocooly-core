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

	public static function handlePostRequest( $key ) {
		$routes = collect( self::$routes['post'] );

		$routesCollection = $routes->filter(
			function( $r ) use ( $key ) {
				return $r['name'] === $key;
			}
		);

		Assert::notEmpty( $routesCollection->toArray(), sprintf( 'Route %s was not found', $key ) );

		if ( $_POST && ! empty( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			DispatchCallable::dispatch( $routesCollection->first()['callback'] );
			return true;
		}
		return false;
	}
}
