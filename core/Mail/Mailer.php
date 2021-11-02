<?php
/**
 * Mailer
 *
 * @package Brocooly-core
 * @since Brocooly 0.16.3 
 */

declare(strict_types=1);

namespace Brocooly\Mail;

use Timber\Timber;
use Brocooly\Router\View;
use Webmozart\Assert\Assert;

class Mailer
{

	private $mailTo;


	private $subject;


	private $message;


	private $headers = '';


	private $attachments = [];

	/**
	 * Send email to resipeint
	 *
	 * @param string|array $mailTo | email (or array of emails) to send to
	 * @return $this
	 */
	public function to( $mailTo ) {
		$this->mailTo = $mailTo;
		return $this;
	}

	/**
	 * Set email subject
	 *
	 * @param string $subject | email subject.
	 * @return 4this
	 */
	public function subject( string $subject ) {
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Set plain text message
	 *
	 * @param string $message | message to send
	 * @return $this
	 */
	public function message( string $message ) {
		$this->message = $message;
		return $this;
	}

	/**
	 * Set email template
	 *
	 * @param string $template | template name
	 * @param array $ctx | template context
	 * @return $this
	 */
	public function template( string $template, array $ctx = [] ) {
		$context       = array_merge( $ctx, Timber::context() );
		$message       = View::compile( $template, $context );
		$this->message = $message;
		return $this;
	}

	/**
	 * Set email header
	 *
	 * @param string|array $headers
	 * @return $this
	 */
	public function headers( $headers = '' ) {
		$this->headers = $headers;
		return $this;
	}

	/**
	 * Set email attachments
	 *
	 * @param array $attachments | array of attachments
	 * @return $this
	 */
	public function attachments( $attachments = [] ) {
		$this->attachments = $attachments;
		return $this;
	}

	/**
	 * Set mailable object
	 *
	 * @param string $mailer | mailer class
	 * @return void
	 */
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

	/**
	 * Send email
	 *
	 * @return void
	 */
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
