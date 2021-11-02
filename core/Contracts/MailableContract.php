<?php

declare(strict_types=1);

namespace Brocooly\Contracts;

interface MailableContract
{
	public function getSubject();

	public function getMessage();

	public function getHeaders();

	public function getAttachments();

	public function template( string $template, array $ctx = [] );

	public function build();
}
