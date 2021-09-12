<?php

declare(strict_types=1);

namespace Brocooly\Mail;

use Brocooly\Router\View;

abstract class Mailable
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

	public function template( string $template, array $ctx ) {
		return View::compile( $template, $ctx );
	}

	abstract public function build();
}
