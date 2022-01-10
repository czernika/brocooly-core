<?php

declare(strict_types=1);

namespace Brocooly\Providers;

use Brocooly\App;
use PHPMailer\PHPMailer\PHPMailer;

class MailServiceProvider extends AbstractService
{

	private array $mailFrom;

	private string $mailFromName;

	private string $mailFromAddress;

	private $mailer;

	private string $defaultMailer;

	private string $transport;

	private string $mailType;

	public function __construct( App $app ) {
		$this->mailFrom        = config( 'mail.from', [] );
		$this->mailFromName    = $this->mailFrom['name'];
		$this->mailFromAddress = $this->mailFrom['address'];

		$this->defaultMailer = config( 'mail.default' );
		$this->mailer        = config( 'mail.mailers' )[ $this->defaultMailer ];
		$this->transport     = $this->mailer['transport'];

		$this->mailType = config( 'mail.type' );

		parent::__construct( $app );
	}

	public function boot() {

		if ( 'smtp' === $this->transport ) {
			add_action( 'phpmailer_init', [ $this, 'setSMTPCredentials' ] );
		}

		if ( 'mailhog' === $this->transport ) {
			add_action( 'phpmailer_init', [ $this, 'setMailHogCredentials' ] );
		}

		add_action( 'wp_mail_content_type', [ $this, 'setContentType' ] );
		add_filter( 'wp_mail_from', [ $this, 'setMailFromAddress' ] );
		add_filter( 'wp_mail_from_name', [ $this, 'setMailFromName' ] );
	}

	public function setSMTPCredentials( PHPMailer $mailer ) {
		$mailer->IsSMTP();
		$mailer->SMTPAutoTLS = false;

		$mailer->SMTPAuth   = true;
		$mailer->SMTPSecure = $this->mailer['encryption'];

		$mailer->Host     = $this->mailer['host'];
		$mailer->Port     = $this->mailer['port'];
		$mailer->Username = $this->mailer['username'];
		$mailer->Password = $this->mailer['password'];

		return $mailer;
	}

	public function setMailHogCredentials( PHPMailer $mailer ) {
		$mailer->IsSMTP();
		$mailer->SMTPAuth = false;

		$mailer->Host = $this->mailer['host'];
		$mailer->Port = $this->mailer['port'];

		return $mailer;
	}

	public function setContentType( string $contentType ) {
		return $this->mailType ?? 'text/html';
	}

	public function setMailFromAddress( string $from ) {
		$appFrom = $this->mailFromAddress;
		if ( $appFrom ) {
			return $appFrom;
		}

		return $from;
	}

	public function setMailFromName( string $from ) {
		$appFrom = $this->mailFromName;
		if ( $appFrom ) {
			return $appFrom;
		}

		return $from;
	}
}
