<?php

declare(strict_types=1);

namespace Brocooly\Support\Builders;

use PHPMailer\PHPMailer\PHPMailer;

use function Env\env;

class Mail
{

	private string $to = '';

	private string $subject = '';

	private string $message = '';

	private array $headers = [];

	private array $attachments = [];

	public function to( string $to ) {
		$this->to = $to;
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

	public function headers( array $headers ) {
		$this->headers = $headers;
		return $this;
	}

	public function attachments( array $attachments ) {
		$this->attachments = $attachments;
		return $this;
	}

	public function send( string $to = '', $subject = '', $message = '' ) {

		$this->beforeSend();

		if ( is_callable( $message ) ) {
			$message = call_user_func( $message );
		}

		$emailTo      = strlen( $to ) ? $to : $this->to;
		$emailSubject = strlen( $subject ) ? $subject : $this->subject;
		$emailMessage = strlen( $message ) ? $message : $this->message;

		return wp_mail(
			$emailTo,
			$emailSubject,
			$emailMessage,
			$this->headers,
			$this->attachments,
		);
	}

	private function beforeSend() {
		add_action( 'phpmailer_init', [ $this, 'setCredentials' ] );

		add_filter( 'wp_mail_from', env( 'MAIL_FROM' ) );

		if ( env( 'MAIL_FROM_NAME' ) ) {
			add_filter('wp_mail_from_name', env( 'MAIL_FROM_NAME' ) );
		}

		add_filter( 'wp_mail_content_type', [ $this, 'setEmailContentType' ] );
	}

	private function setCredentials( PHPMailer $mail ) {
		$mail->IsSMTP();
		$mail->SMTPAutoTLS = false;

		$mail->SMTPAuth   = true;
		$mail->SMTPSecure = env( 'MAIL_ENCRYPTION' ) ?? 'tls';

		$mail->Host     = env( 'MAIL_HOST' );
		$mail->Port     = env( 'MAIL_PORT' ) ?? 587;
		$mail->Username = env( 'MAIL_USERNAME' );
		$mail->Password = env( 'MAIL_PASSWORD' );

		return $mail;
	}

	private function setEmailContentType( string $type = 'text/html' ) {
		return $type;
	}
}
