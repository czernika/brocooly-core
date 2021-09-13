<?php
/**
 * Create mail model
 *
 * @example
 * ```
 * php broccoli new:mail <MailName>
 * ```
 *
 * @package Brocooly-core
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Brocooly\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMenu extends CreateFileConsoleCommand
{

	/**
	 * The name of the command
	 */
	protected static $defaultName = 'new:mail';

	/**
	 * Mail name
	 *
	 * @var string
	 */
	private string $mail;

	/**
	 * Shortcode stub file model
	 *
	 * @var string
	 */
	protected string $stubModelName = 'mail.stub';

	/**
	 * Set arguments for `configure()` method
	 */
	protected function setArguments() {
		$this
			->setDescription( 'Allows you to create new mail' )
			->addArgument(
				'mail',
				InputArgument::REQUIRED,
				'Mail class name'
			);
	}

	/**
	 * Set arguments for `execute()` method
	 */
	protected function preexecute( InputInterface $input, OutputInterface $output ) {
		$this->mail = $input->getArgument( 'mail' );

		$this->createFile(
			$output,
			$this->mail,
			'Mail/',
			'Mail.php',
		);

		return $this->success( $output, 'Mail was successfully created' );
	}

	/**
	 * Replace variables inside stub file
	 *
	 * @param string $value | value to handle inside.
	 * @return array
	 */
	protected function searchAndReplace( string $value ) {
		return [
			'{{ MAIL }}' => Str::ucfirst( $value ),
		];
	}
}
