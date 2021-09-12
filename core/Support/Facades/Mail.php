<?php

declare(strict_types=1);

namespace Brocooly\Support\Facades;

use Brocooly\Mail\Mailer;

class Mail extends AbstractFacade
{
	protected static $factory = Mailer::class;
}
