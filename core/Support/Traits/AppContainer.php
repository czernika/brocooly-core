<?php

declare(strict_types=1);

namespace Brocooly\Support\Traits;

use Psr\Container\ContainerInterface;

use function DI\factory;
use function DI\autowire;

trait AppContainer
{

	/**
	 * Bind Interface with it's class object
	 *
	 * @inheritdoc
	 */
	public function bind(string $key, string $value)
	{
		$this->container->set(
			$key,
			factory(
				function (ContainerInterface $container) use ($value) {
					return $container->get($value);
				}
			)
		);
	}

	/**
	 * Wire key with it's value using DI\Container
	 *
	 * @inheritdoc
	 */
	public function wire(string $key, string $value)
	{
		$this->container->set($key, autowire($value));
	}

	/**
	 * Get key from App Container
	 *
	 * @param string $key | key to get.
	 * @return mixed
	 */
	public function get($id)
	{
		return $this->container->get($id);
	}

	/**
	 * Set value into App Container
	 *
	 * @param string $key | key name.
	 * @param mixed  $value | key value.
	 */
	public function set($key, $value)
	{
		$this->container->set($key, $value);
	}

	/**
	 * Check if is value exists in App Container
	 *
	 * @param string $key | key to check.
	 * @return boolean
	 */
	public function has($key)
	{
		return $this->container->has($key);
	}

	/**
	 * Inject dependencies into object
	 *
	 * @param $object | instance of class to inject on.
	 */
	public function injectOn($object)
	{
		return $this->container->injectOn($object);
	}

	/**
	 * Create instance of object
	 *
	 * @param string $name | object name.
	 * @param array  $parameters | additional data to pass.
	 * @return object
	 */
	public function make( $name, $parameters = [] )
	{
		return $this->container->make( $name, $parameters );
	}

	/**
	 * @inheritDoc
	 */
	public function call( $callable, $parameters = [] )
	{
		return $this->container->call( $callable, $parameters );
	}
}
