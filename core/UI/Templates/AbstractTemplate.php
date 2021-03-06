<?php
/**
 * Custom templates for pages.
 * No need to create empty template.php file - just pass information wright here
 *
 * Template file should be listed in templates/template.YOUR_SLUG.twig file.
 * YOUR_SLUG must be the same as slug in array of arguments.
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\UI\Templates;

abstract class AbstractTemplate
{

	/**
	 * Template post type
	 *
	 * @var array
	 */
	public array $postTypes = [ 'page' ];

	/**
	 * Template slug
	 *
	 * @var string
	 */
	const SLUG = 'custom';

	/**
	 * Get template label
	 *
	 * @return string
	 */
	public function label() {
		return esc_html__( 'Custom template', 'brocooly' );
	}
}
