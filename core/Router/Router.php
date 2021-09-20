<?php
/**
 * Router
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Router;

use Illuminate\Support\Str;

class Router
{
	private Routes $routes;

	private bool $route_was_hit = false;

	private array $allowedConditionals = [
		'is_404',
		'is_admin',
		'is_archive',
		'is_attachment',
		'is_author',
		'is_blog_admin',
		'is_category',
		'is_comment_feed',
		'is_customize_preview',
		'is_date',
		'is_day',
		'is_embed',
		'is_favicon',
		'is_feed',
		'is_front_page',
		'is_home',
		'is_month',
		'is_network_admin',
		'is_page',
		'is_page_template',
		'is_paged',
		'is_post_type_archive',
		'is_preview',
		'is_privacy_policy',
		'is_robots',
		'is_rtl',
		'is_search',
		'is_single',
		'is_singular',
		'is_ssl',
		'is_sticky',
		'is_tag',
		'is_tax',
		'is_time',
		'is_trackback',
		'is_user_admin',
		'is_year'
	];

	public function __construct( Routes $routes ) {
		$this->routes = $routes;
	}

	public function __call( $condition, $args ) {
		if ( ! in_array( Str::snake( $condition ), $this->allowedConditionals, true ) ) {
			return;
		}

		[ $callback ] = $args;
		$this->routes->addRoute( 'get', Str::snake( $condition ), $callback );
	}

	public function get( $condition, $callback ) {
		$this->routes->addRoute( 'get', $condition, $callback );
	}

	public function view( $condition, $template ) {
		$this->routes->addRoute(
			'get',
			$condition,
			function() use ( $template ) {
				view( $template );
			},
		);
	}

	public function post( $condition, $callback ) {
		$this->routes->addRoute( 'post', $condition, $callback );
	}

	public function ajax( $action, $callback ) {
		$this->routes->addRoute( 'ajax', $action, $callback );
	}

	public static function action( $key ) {
		return RequestHandler::handlePostRequest( $key );
	}

	public function resolve() {
		if ( ! $this->route_was_hit ) {
			$this->route_was_hit = RequestHandler::defineRoute( $this->routes->getRoutes() );
		}

		// Default error handler.
		if ( ! $this->route_was_hit ) {
			View::throw404();
		}

		$this->resolveAjax();
	}

	private function resolveAjax() {
		RequestHandler::handleAjaxRequest();
	}
}
