<?php
/**
 * Template Service Provider
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Providers;

use Brocooly\App;

class TemplateServiceProvider extends AbstractService
{

	private array $templates;

	public function __construct( App $app ) {
		$this->templates = config( 'views.templates', [] );

		parent::__construct( $app );
	}

	public function boot() {
		foreach ( $this->templates as $template ) {
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
