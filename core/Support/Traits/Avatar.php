<?php

declare(strict_types=1);

namespace Brocooly\Support\Traits;

use Brocooly\Support\Facades\Meta;

trait Avatar
{
    public function userAvatar() {
		$this->createFields(
			'avatar',
			esc_html__( 'User avatar', 'brocooly' ),
			[
				Meta::image( 'avatar_url', esc_html__( 'Image', 'brocooly' ) )
					->set_value_type( 'url' ),
			],
			'side',
		);
	}
}
