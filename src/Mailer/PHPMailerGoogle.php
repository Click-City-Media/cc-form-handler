<?php

namespace CCMedia\FormHandler\Mailer;

use CCMedia\FormHandler\DataObjects\DataObject;
use CCMedia\FormHandler\DataObjects\MailMessage;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\Exception as PhpMailerException;

use League\OAuth2\Client\Provider\Google;

/**
 * Class PHPMailer
 *
 * @package CCMedia\FormHandler\Mailer
 */
class PHPMailerGoogle extends DataObject implements MailerInterface
{
	/**
	 * Attachments size limit
	 *
	 * @var int
	 */
	protected $attachmentsSizeLimit = 8000000;

	/**
	 * List of errors
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Email address
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * ClientId
	 *
	 * @var string
	 */
	protected $clientId;

	/**
	 * Secret
	 *
	 * @var string
	 */
	protected $clientSecret;

	/**
	 * Secret
	 *
	 * @var string
	 */
	protected $refreshToken;

	/**
	 * DEBUG
	 */
	protected $debugLevel = SMTP::DEBUG_OFF

	/**
	 * Sending form
	 *
	 * @param MailMessage $message User message
	 *
	 * @return bool
	 */
	public function send(MailMessage $message)
	{
		$mail = new PHPMailer(true);               // Passing `true` enables exceptions.
		try {
			$mail->isSMTP();
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;
			$mail->Host = 'smtp.gmail.com';
			$mail->Port = 587;
			$mail->SMTPSecure = $this->debugLevel;
			$mail->SMTPAuth = true;
			$mail->AuthType = 'XOAUTH2';

			//Create a new OAuth2 provider instance
			$provider = new Google(
				[
					'clientId' => $this->clientId,
					'clientSecret' => $this->clientSecret,
				]
			);

			//Pass the OAuth provider instance to PHPMailer
			$mail->setOAuth(
				new OAuth(
					[
						'provider' => $provider,
						'clientId' => $this->clientId,
						'clientSecret' => $this->clientSecret,
						'refreshToken' => $this->refreshToken,
						'userName' => $this->email,
					]
				)
			);


			// Set From.
			if ($address = $message->getFrom()) {
				$mail->setFrom($address->getEmail(), $address->getName());
			}

			// Set Reply To.
			if ($replyTo = $message->getReplyTo()) {
				$mail->addReplyTo($replyTo->getEmail(), $replyTo->getName());
			}

			// Recipients.
			if ($to = $message->getTo()) {
				foreach ($to as $address) {
					$mail->addAddress($address->getEmail(), $address->getName());
				}
			}

			if ($cc = $message->getCc()) {
				foreach ($cc as $address) {
					$mail->addCC($address->getEmail(), $address->getName());
				}
			}

			if ($bcc = $message->getBcc()) {
				foreach ($bcc as $address) {
					$mail->addBCC($address->getEmail(), $address->getName());
				}
			}

			// Attachments.
			if (
				0 < $message->getAttachmentsSize() && $message->getAttachmentsSize() < $this->attachmentsSizeLimit
				&& $attachments = $message->getAttachments()
			) {
				foreach ($attachments as $attachment) {
					$mail->addAttachment($attachment->uploadPath, $attachment->name);
				}
			}

			// Content.
			$mail->Subject = $message->getSubject();
			if ($body = $message->getBody()) {
				$mail->isHTML(true);
				$mail->Body    = $message->getBody();
				$mail->AltBody = $message->getAltBody();
			} else {
				$mail->Body = $message->getAltBody();
			}

			$this->errors = array();

			return $mail->send();
		} catch (PhpMailerException $e) {
			$this->errors[] = 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
			return false;
		}
	}

	/**
	 * Getting list of errors
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}
}
