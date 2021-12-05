<?php
/**
 * Route facade.
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Support\Facades;

/**
 * @method static $this get( $condition, $callback )
 * @method static $this view( $condition, $template )
 * @method static $this post( $action, $callback )
 * @method static $this ajax( $action, $callback )
 * @method static $this name( string $named )
 * @method static $this noPriv()
 * @method static void mailable( $mailer )
 * @method static void send()
 */
class Route extends AbstractFacade
{
	protected static $factory = 'routing';
}
