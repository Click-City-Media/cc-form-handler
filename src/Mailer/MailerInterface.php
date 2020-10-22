<?php

namespace CCMedia\FormHandler\Mailer;

use CCMedia\FormHandler\DataObjects\MailMessage;

interface MailerInterface
{
	/**
	 * Sending email
	 *
	 * @param MailMessage $message MailMessage
	 *
	 * @return mixed
	 */
	public function send(MailMessage $message);

	/**
	 * Getting errors
	 *
	 * @return mixed
	 */
	public function getErrors();
}
