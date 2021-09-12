<?php

declare(strict_types=1);

namespace Brocooly\Providers;

use PHPMailer\PHPMailer\PHPMailer;

class MailServiceProvider extends AbstractService
{

	public function register() {

		$mailFrom = config( 'mail.from' );

		$this->app->set( 'mail.from_name', $mailFrom['name'] );
		$this->app->set( 'mail.from_address', $mailFrom['address'] );

		$mailer = config( 'mail.default' );
		$mail   = config( 'mail.mailers' )[ $mailer ];

		$this->app->set( 'mail.mailer', $mail['transport'] );

		if ( 'smtp' === $mailer ) {
			$this->setSMTP( $mail );
		}
	}

	public function boot() {
		if ( 'smtp' === $this->app->get( 'mail.mailer' ) ) {
			add_action( 'phpmailer_init', [ $this, 'setCredentials' ] );
		}

		add_action( 'wp_mail_content_type', [ $this, 'setContentType' ] );
		add_filter( 'wp_mail_from', [ $this, 'setMailFromAddress' ] );
		add_filter( 'wp_mail_from_name', [ $this, 'setMailFromName' ] );
	}

	private function setSMTP( array $mail ) {
		$this->app->set( 'mail.encryption', $mail['encryption'] );
		$this->app->set( 'mail.host', $mail['host'] );
		$this->app->set( 'mail.port', $mail['port'] );
		$this->app->set( 'mail.username', $mail['username'] );
		$this->app->set( 'mail.password', $mail['password'] );
	}

	public function setCredentials( PHPMailer $mailer ) {
		$mailer->IsSMTP();
		$mailer->SMTPAutoTLS = false;

		$mailer->SMTPAuth   = true;
		$mailer->SMTPSecure = $this->app->get( 'mail.encryption' );

		$mailer->Host     = $this->app->get( 'mail.host' );
		$mailer->Port     = $this->app->get( 'mail.port' );
		$mailer->Username = $this->app->get( 'mail.username' );
		$mailer->Password = $this->app->get( 'mail.password' );

		return $mailer;
	}

	public function setContentType( string $contentType ) {
		return "text/html";
	}

	public function setMailFromAddress( string $from ) {
		$appFrom = $this->app->get( 'mail.from_address' );
		if ( $appFrom ) {
			return $appFrom;
		}

		return $from;
	}

	public function setMailFromName( string $from ) {
		$appFrom = $this->app->get( 'mail.from_name' );
		if ( $appFrom ) {
			return $appFrom;
		}

		return $from;
	}
}