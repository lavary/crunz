<?php

namespace Crunz;

use Crunz\Configuration\Configuration;

class Mailer
{
    /**
     * Mailer instance.
     *
     * @param \Swift_Mailer
     */
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
     * @param string $subject
     * @param string $message
     */
    public function send($subject, $message)
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
     * @return \Swift_Mailer
     */
    protected function getMailer()
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
            $transport = $this->getMailTransport();
            break;

            default:
            $transport = $this->getSendMailTransport();
        }

        $this->mailer = \method_exists(\Swift_Mailer::class, 'newInstance')
            ? \Swift_Mailer::newInstance($transport)
            : new \Swift_Mailer($transport)
        ;

        return $this->mailer;
    }

    /**
     * Get the SMTP transport.
     *
     * @return \Swift_SmtpTransport
     */
    protected function getSmtpTransport()
    {
        $object = method_exists(\Swift_SmtpTransport::class, 'newInstance')
            ? \Swift_SmtpTransport::newInstance(
                $this->config('smtp.host'),
                $this->config('smtp.port'),
                $this->config('smtp.encryption')
            )
            : new \Swift_SmtpTransport(
                $this->config('smtp.host'),
                $this->config('smtp.port'),
                $this->config('smtp.encryption')
            );

        return $object
        ->setUsername($this->config('smtp.username'))
        ->setPassword($this->config('smtp.password'));
    }

    /**
     * Get the Mail transport.
     *
     * @return \Swift_MailTransport
     */
    protected function getMailTransport()
    {
        if (!class_exists('\Swift_MailTransport')) {
            throw new \Exception('Mail transport has been removed in SwiftMailer 6');
        }

        return \Swift_MailTransport::newInstance();
    }

    /**
     * Get the Sendmail Transport.
     *
     * @return \Swift_SendmailTransport
     */
    protected function getSendMailTransport()
    {
        return method_exists(\Swift_SendmailTransport::class, 'newInstance')
            ? \Swift_SendmailTransport::newInstance()
            : new \Swift_SendmailTransport();
    }

    /**
     * Prepare a swift message object.
     *
     * @param string $subject
     * @param string $message
     *
     * @return \Swift_Message
     */
    protected function getMessage($subject, $message)
    {
        $object = method_exists(\Swift_Message::class, 'newInstance')
            ? \Swift_Message::newInstance()
            : new \Swift_Message();

        return  $object
                 ->setBody($message)
                 ->setSubject($subject)
                 ->setFrom([$this->config('mailer.sender_email') => $this->config('mailer.sender_name')])
                 ->setTo($this->config('mailer.recipients'));
    }

    private function config($key)
    {
        return $this->configuration
            ->get($key)
        ;
    }
}
