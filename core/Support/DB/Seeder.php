<?php

declare(strict_types=1);

namespace Brocooly\Support\DB;

use Faker\Factory;

class Seeder
{

	protected $seeder = 'post';

	protected $times = 1;

	public $faker;

	public function __construct() {
		$this->faker = Factory::create();
	}

	public function run() {

		if ( ! $this->seeder ) {
			throw new \Exception( 'Seeder requires post type' );
		}

		for ( $i = 0; $i < $this->times; $i++) {
			app( $this->seeder )->create( $this->params() );
		}
	}

	protected function params() {
		return [];
	}
}
