<?php

declare(strict_types=1);

namespace Brocooly\Support\DB;

use Faker\Factory;

class Seeder
{

	/**
	 * Seeder object
	 *
	 * @var string
	 */
	protected string $seeder = 'post';

	/**
	 * Amount of times Seeder will run `params()` method
	 *
	 * @var integer
	 */
	protected int $times = 1;

	/**
	 * Faker object
	 *
	 * @var object
	 */
	public $faker;

	public function __construct() {
		$this->faker = Factory::create();
	}

	public function run() {
		if ( ! $this->seeder ) {
			throw new \Exception( 'Seeder requires post type' );
		}

		$seeder = app( $this->seeder );
		$params = $this->params();

		if ( $params && ! empty( $params ) ) {
			for ( $i = 0; $i < $this->times; $i++) {
				$seeder->create( $params );
			}
		}

		if ( method_exists( $seeder, 'execute' ) ) {
			$seeder->execute();
		}
	}

	/**
	 * Return seeder params
	 *
	 * @return array|null
	 */
	protected function params() {
		return null;
	}
}
