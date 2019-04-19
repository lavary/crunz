<?php

declare(strict_types=1);

namespace Crunz;

use Crunz\Configuration\Configuration;
use Crunz\Exception\MailerException;

class Mailer
{
    /** @var \Swift_Mailer|null */
    protected $mailer;
    /** @var Configuration */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Send an email.
     *
     * @throws MailerException
     */
    public function send(string $subject, string $message): void
    {
        $this->getMailer()
            ->send(
                $this->getMessage($subject, $message)
            )
        ;
    }

    /**
     * Return the proper mailer.
     *
     * @throws MailerException
     */
    private function getMailer(): \Swift_Mailer
    {
        // If the mailer has already been defined via the constructor, return it.
        if ($this->mailer) {
            return $this->mailer;
        }

        // Get the proper transporter
        switch ($this->config('mailer.transport')) {
            case 'smtp':
                $transport = $this->getSmtpTransport();

                break;

            case 'mail':
                throw new MailerException(
                    "'mail' transport is no longer supported, please use 'smtp' or 'sendmail' transport."
                );

                break;

            default:
                $transport = $this->getSendMailTransport();
        }

        $this->mailer = new \Swift_Mailer($transport);

        return $this->mailer;
    }

    /**
     * Get the SMTP transport.
     */
    private function getSmtpTransport(): \Swift_SmtpTransport
    {
        $object = new \Swift_SmtpTransport(
            $this->config('smtp.host'),
            $this->config('smtp.port'),
            $this->config('smtp.encryption')
        );

        return $object
            ->setUsername($this->config('smtp.username'))
            ->setPassword($this->config('smtp.password'))
        ;
    }

    /**
     * Get the Sendmail Transport.
     */
    private function getSendMailTransport(): \Swift_SendmailTransport
    {
        return new \Swift_SendmailTransport();
    }

    /**
     * Prepare a swift message object.
     */
    private function getMessage(string $subject, string $message): \Swift_Message
    {
        $messageObject = new \Swift_Message($subject, $message);
        $messageObject
            ->setFrom([$this->config('mailer.sender_email') => $this->config('mailer.sender_name')])
            ->setTo($this->config('mailer.recipients'))
        ;

        return $messageObject;
    }

    private function config($key)
    {
        return $this->configuration
            ->get($key)
        ;
    }
}
