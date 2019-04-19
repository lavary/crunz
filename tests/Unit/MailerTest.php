<?php

declare(strict_types=1);

namespace Crunz\Tests\Unit;

use Crunz\Configuration\Configuration;
use Crunz\Exception\MailerException;
use Crunz\Mailer;
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
        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock
            ->method('get')
            ->with('mailer.transport')
            ->willReturn($transport)
        ;

        return new Mailer($configurationMock);
    }
}
