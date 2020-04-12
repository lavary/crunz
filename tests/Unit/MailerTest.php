<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit;

use Crunz\Exception\MailerException;
use Crunz\Mailer;
use Crunz\Tests\TestCase\FakeConfiguration;
use PHPUnit\Framework\TestCase;

final class MailerTest extends TestCase
{
    /** @test */
    public function usingMailTransportWillResultInException(): void
    {
        $this->expectException(MailerException::class);
        $this->expectExceptionMessage("'mail' transport is no longer supported, please use 'smtp' or 'sendmail' transport.");

        $mailer = $this->createMailer('mail');
        $mailer->send('Test', 'Message');
    }

    private function createMailer(string $transport): Mailer
    {
        $configuration = new FakeConfiguration(
            [
                'mailer' => [
                    'transport' => $transport,
                ],
            ]
        );

        return new Mailer($configuration);
    }
}
