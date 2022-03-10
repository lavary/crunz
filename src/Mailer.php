<?php

declare(strict_types=1);

namespace Crunz;

use Crunz\Application\Service\ConfigurationInterface;
use Crunz\Exception\MailerException;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class Mailer
{
    /** @var SymfonyMailer|null */
    protected $mailer;
    /** @var ConfigurationInterface */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
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
    private function getMailer(): SymfonyMailer
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

            default:
                $transport = $this->getSendMailTransport();
        }

        $this->mailer = new SymfonyMailer($transport);

        return $this->mailer;
    }

    private function getSmtpTransport(): Transport\TransportInterface
    {
        $host = $this->config('smtp.host');
        $port = $this->config('smtp.port');
        $encryption = \filter_var($this->config('smtp.encryption') ?? true, FILTER_VALIDATE_BOOLEAN);
        $user = $this->config('smtp.username');
        $password = $this->config('smtp.password');
        $encryptionString = $encryption
            ? 1
            : 0
        ;
        $userPart = null !== $user && null !== $password
            ? "{$user}:{$password}@"
            : ''
        ;

        $dsn = "smtp://{$userPart}{$host}:{$port}?verifyPeer={$encryptionString}";

        return Transport::fromDsn($dsn);
    }

    private function getSendMailTransport(): Transport\TransportInterface
    {
        $dsn = 'sendmail://default';

        return Transport::fromDsn($dsn);
    }

    private function getMessage(string $subject, string $message): Email
    {
        $from = new Address($this->config('mailer.sender_email'), $this->config('mailer.sender_name'));
        $messageObject = new Email();
        $messageObject
            ->from($from)
            ->subject($subject)
            ->text($message)
        ;
        foreach ($this->config('mailer.recipients') ?? [] as $recipient) {
            $messageObject->addTo($recipient);
        }

        return $messageObject;
    }

    /** @return mixed */
    private function config(string $key)
    {
        return $this->configuration
            ->get($key)
        ;
    }
}
