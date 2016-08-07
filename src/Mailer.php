<?php

namespace Crunz;

use Crunz\Configuration\Configurable;

class Mailer extends Singleton {

    use Configurable;

    /**
     * Mailer instance
     *
     * @param \Swift_Mailer
     */
    protected $mailer;

    /**
     * Instantiate the Mailer class
     *
     * @param \Swift_Mailer $mailer
     */
    public function __construct(\Swift_Mailer $mailer = null)
    {
        $this->configurable();
        $this->mailer = $mailer;
    }

    /**
     * Return the proper mailer
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
            $transport = $this->getMailTranport();
            break;
            
            default:
            $transport = $this->getSendMailTransport();
        }

        return \Swift_Mailer::newInstance($transport);
    }

    /**
     * Get the SMTP transport
     *
     * @return \Swift_SmtpTransport
     */
    protected function getSmtpTransport()
    {
      return \Swift_SmtpTransport::newInstance(               
            
            $this->config('smtp.host'),
            $this->config('smtp.port'),
            $this->config('smtp.encryption')

        )
        ->setUsername($this->config('smtp.username'))
        ->setPassword($this->config('smtp.password'));
    }

    /**
     * Get the Mail transport
     *
     * @return \Swift_SendmailTransport
     */
    protected function getMailTrasport()
    {
        return \Swift_MailTransport::newInstance();
    }

    /**
     * Get the Sendmail Transport
     *
     * @return \Swift_SendmailTransport
     */
    protected function getSendMailTransport()
    {
        return \Swift_SendmailTransport::newInstance();
    }

    /**
     * Send an email
     *
     * @param  string $subject
     * @param  string $message
     * 
     * @return boolean 
     */
    public function send($subject, $message)
    {
        $this->getMailer()->send($this->getMessage($subject, $message));
    }

    /**
     * Prepare a swift message object
     *
     * @param  string $subject
     * @param  string $message
     * 
     * @return \Swift_Message
     *
     */
    protected function getMessage($subject, $message)
    {
        return  \Swift_Message::newInstance()
                 
                 ->setBody($message)
                 ->setSubject($subject)
                 ->setFrom([$this->config('mailer.sender_email') => $this->config('mailer.sender_name')])
                 ->setTo($this->config('mailer.recipients'));
    }

}