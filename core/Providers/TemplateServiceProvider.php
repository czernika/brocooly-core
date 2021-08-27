<?php
/**
 * Template Service Provider
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

class TemplateServiceProvider extends AbstractService
{

	public function register() {
		$this->app->set( 'templates', config( 'views.templates', [] ) );
	}

	public function boot() {
		$templates = $this->app->get( 'templates' );

		foreach ( $templates as $template ) {
			$tpl = $this->app->make( $template );

			foreach ( $tpl->postTypes as $postType ) {
				add_filter(
					"theme_${postType}_templates",
					function ( $post_templates, $theme, $post, $post_type ) use ( $tpl ) {
						$post_templates[ $tpl::SLUG ] = $tpl->label();
						return $post_templates;
					},
					10,
					4,
				);
			}
		}
	}
}
