<?php

declare(strict_types=1);

namespace Jp\Jpfaq\Tests\Functional\Service;

use Jp\Jpfaq\Service\SendMailService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(SendMailService::class)]
final class SendMailServiceTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['jvdp/jpfaq'];

    protected array $configurationToUseInTestInstance = [
        'MAIL' => [
            'transport' => 'mbox',
            'transport_mbox_file' => '/tmp/jpfaq-functional-mbox',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        @unlink('/tmp/jpfaq-functional-mbox');
    }

    protected function tearDown(): void
    {
        @unlink('/tmp/jpfaq-functional-mbox');
        parent::tearDown();
    }

    #[Test]
    public function sendsTheMailToAllReceivers(): void
    {
        $sent = SendMailService::sendMail(
            'info@example.com, marketing@example.com',
            ['no-reply@example.com' => 'Website FAQ'],
            'New FAQ comment',
            '<p>A new comment</p>',
        );

        self::assertTrue($sent);
        $mbox = (string)file_get_contents('/tmp/jpfaq-functional-mbox');
        self::assertStringContainsString('To: info@example.com, marketing@example.com', $mbox);
        self::assertStringContainsString('From: Website FAQ <no-reply@example.com>', $mbox);
        self::assertStringContainsString('Subject: New FAQ comment', $mbox);
        self::assertStringContainsString('<p>A new comment</p>', $mbox);
    }
}
