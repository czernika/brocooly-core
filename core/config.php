<?php
/**
 * Configuration object for DI Container instance.
 *
 * @link https://php-di.org/doc/php-definitions.html
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

use Brocooly\App;
use Timber\Timber;
use Brocooly\Router\Router;
use Brocooly\Contracts\ModelContract;
use Brocooly\Support\Factories\MetaFactory;
use Brocooly\Contracts\AppContainerInterface;
use Brocooly\Support\Factories\FacadeFactory;
use Brocooly\Support\Factories\PostTypeFactory;
use Brocooly\Support\Factories\ValidatorFactory;
use Brocooly\Support\Factories\CustomizerFactory;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

use function DI\get;
use function DI\create;
use function DI\factory;

$appDefintions = [

	/**
	 *--------------------------------------------------------------------------
	 * Main Application instances
	 *--------------------------------------------------------------------------
	 *
	 * Application itself and Timber class. As Brocooly depends on Timber
	 * this is a core of application.
	 */
	'timber'             => get( Timber::class ),
	'routing'            => get( Router::class ),

	/**
	 *--------------------------------------------------------------------------
	 * App Facades
	 *--------------------------------------------------------------------------
	 */
	'mod'                => create( CustomizerFactory::class ),
	'meta'               => create( MetaFactory::class ),
	'facade'             => create( FacadeFactory::class ),
	'validator'          => create( ValidatorFactory::class ),

	/**
	 * --------------------------------------------------------------------------
	 * Factories
	 * --------------------------------------------------------------------------
	 */
	AppContainerInterface::class => get( App::class ),
	ModelContract::class         => factory( [ PostTypeFactory::class, 'model' ] ),
	'session'                    => function( $c ) {
		return new Session();
	},

];

return $appDefintions;
