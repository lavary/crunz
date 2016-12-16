<?php

namespace Crunz;

use Crunz\Logger\LoggerFactory;
use Crunz\Configuration\Configurable; 

class ErrorHandler extends Singleton {
    
    use Configurable;

    /**
     * Instance of the logger
     *
     * @var \Crunz\Logger
     */
    protected $logger;

    /**
     * Instance of the Mailer
     *
     * @var \Crunz\Mailer
     */
    protected $mailer;

    /**
     * Catch Fatal Errors
     *
     */
     public function __construct()
     {
        $this->configurable();

        $this->logger = LoggerFactory::makeOne([
            'error' => $this->config('errors_log_file'),
        ]);

        $this->mailer = new Mailer();
     }    

    /**
     * Catch Fatal Errors
     *
     */
    public function set()
    {
        ob_start([&$this, 'catchErrors']);
    }

    /**
     * Determine the type of the output
     *
     * @param  string $buffer
     *
     * @return Boolean
     */
    public function catchErrors($buffer)
    {
        if (!is_null(($error_get_last = error_get_last()))) {

            // Ignore notice type errors
            if ($error_get_last['type'] != E_NOTICE) {

                // add the error data onto the buffer
                print_r($error_get_last);
                $buffer .= ob_get_contents();

                $this->logger->error($buffer);

                // Send error as email as configured
                if ($this->config('email_errors')) {
                    $this->mailer->send(
                        'Crunz: reporting PHP Fatal error',
                        $buffer
                    );
                }
            }
        }

        return $buffer;
    }

}
