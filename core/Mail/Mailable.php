<?php

declare(strict_types=1);

namespace Brocooly\Mail;

use Timber\Timber;
use Brocooly\Router\View;
use Brocooly\Contracts\MailableContract;

abstract class Mailable implements MailableContract
{

	protected $subject;

	protected $message;

	protected $headers;

	protected $attachments;

	public function getSubject() {
		return $this->subject;
	}

	public function getMessage() {
		return $this->message;
	}

	public function getHeaders() {
		return $this->headers;
	}

	public function getAttachments() {
		return $this->attachments;
	}

	public function template( string $template, array $ctx = [] ) {
		$context = array_merge( $ctx, Timber::context() );
		return View::compile( $template, $context );
	}

	abstract public function build();
}
