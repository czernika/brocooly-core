<?php

declare(strict_types=1);

namespace Brocooly\Support\Traits;

trait HasContainer
{
	protected function setContainer( $container ) {
		$this->container = $container;
	}

	public function container() {
		return $this->container;
	}
}
