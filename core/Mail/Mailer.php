<?php

declare(strict_types=1);

namespace Brocooly\Mail;

use Brocooly\Router\View;
use Webmozart\Assert\Assert;

class Mailer
{

	private $mailTo;


	private $subject;


	private $message;


	private $headers = '';


	private $attachments = [];


	public function to( $mailTo ) {
		$this->mailTo = $mailTo;
		return $this;
	}


	public function subject( string $subject ) {
		$this->subject = $subject;
		return $this;
	}


	public function message( string $message ) {
		$this->message = $message;
		return $this;
	}


	public function template( string $template, array $ctx ) {
		$message       = View::compile( $template, $ctx );
		$this->message = $message;
		return $this;
	}


	public function headers( $headers = '' ) {
		$this->headers = $headers;
		return $this;
	}


	public function attachments( $attachments = [] ) {
		$this->attachments = $attachments;
		return $this;
	}


	public function mailable( $mailer ) {

		$mailable = app( $mailer );
		$mailable->build();

		$this->subject     = $mailable->getSubject();
		$this->message     = $mailable->getMessage();
		$this->headers     = $mailable->getHeaders();
		$this->attachments = $mailable->getAttachments();

		Assert::notNull( $this->mailTo, 'Mail recipient is not specified' );

		$this->send();
	}


	public function send() {

		Assert::notNull( $this->mailTo, 'Mail recipient is not specified' );
		Assert::notNull( $this->subject, 'Mail subject is not specified' );
		Assert::notNull( $this->message, 'Mail has no message' );

		return wp_mail(
			$this->mailTo,
			$this->subject,
			$this->message,
			$this->headers,
			$this->attachments,
		);
	}
}
