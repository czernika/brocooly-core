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
use Theme\Containers\Products\Web\Controllers\AjaxSearchController;

class Router
{

	private $routeCollection = [
		'get' => [],
		'post' => [],
		'ajax' => [],
	];

	private $currentRoute = 0;

	private $currentMethod = 'get';

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

	public function __call( $condition, $args ) {
		if ( ! in_array( Str::snake( $condition ), $this->allowedConditionals, true ) ) {
			return;
		}

		[ $callback ] = $args;
		$this->addRoute( 'get', Str::snake( $condition ), $callback );
	}

	private function addRoute( $method, $condition, $callback ) {
		$id = count( $this->routeCollection[ $method ] );

		$this->routeCollection[ $method ][ $id ] = [
			'name'      => $id,
			'condition' => (array) $condition,
			'callback'  => $callback,
			'nopriv'    => false,
		];

		$this->currentRoute  = $id;
		$this->currentMethod = $method;
	}

	public function getRoutes() {
		return $this->routeCollection;
	}

	public function get( $condition, $callback ) {
		$this->addRoute( 'get', $condition, $callback );
		return $this;
	}

	public function view( $condition, $template ) {
		$this->addRoute(
			'get',
			$condition,
			function() use ( $template ) {
				return view( $template );
			},
		);
	}

	public function post( $action, $callback ) {
		$this->addRoute( 'post', $action, $callback );
		return $this;
	}

	public function ajax( $action, $callback ) {
		$this->addRoute( 'ajax', $action, $callback );
		return $this;
	}

	public function name( string $named ) {
		$this->routeCollection[ $this->currentMethod ][ $this->currentRoute ]['name'] = $named;
		return $this;
	}

	public function noPriv() {
		$this->routeCollection[ $this->currentMethod ][ $this->currentRoute ]['nopriv'] = true;
		return $this;
	}

	public function resolve() {
		if ( ! $this->route_was_hit ) {
			$this->route_was_hit = RequestHandler::defineRoute( $this->routeCollection );
		}

		// Default error handler.
		if ( ! $this->route_was_hit ) {
			View::throw404();
		}
	}
}
