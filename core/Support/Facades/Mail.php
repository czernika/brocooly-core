<?php

declare(strict_types=1);

namespace Brocooly\Support\Facades;

use Brocooly\Support\Factories\MailerFactory;

class Mail extends AbstractFacade
{
    protected static $factory = MailerFactory::class;
}
