<?php

declare(strict_types=1);

namespace Brocooly\Support\Facades;

use Brocooly\Mail\Mailer;

/**
 * @method static $this to( $mailTo )
 * @method static $this subject( string $subject )
 * @method static $this message( string $message )
 * @method static $this template( string $template, array $ctx = [] )
 * @method static $this headers( $headers = '' )
 * @method static $this attachments( $attachments = [] )
 * @method static void mailable( $mailer )
 * @method static void send()
 */
class Mail extends AbstractFacade
{
	protected static $factory = Mailer::class;
}
