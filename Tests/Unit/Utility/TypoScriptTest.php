<?php

declare(strict_types=1);

namespace Jp\Jpfaq\Tests\Unit\Utility;

use Jp\Jpfaq\Utility\TypoScript;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(TypoScript::class)]
final class TypoScriptTest extends UnitTestCase
{
    private TypoScript $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new TypoScript();
    }

    #[Test]
    public function emptyFlexFormValueIsTakenFromTypoScript(): void
    {
        $result = $this->subject->override(
            ['gtag' => ''],
            ['settings' => ['overrideFlexformSettingsIfEmpty' => 'gtag', 'gtag' => '1']],
        );

        self::assertSame('1', $result['gtag']);
    }

    #[Test]
    public function zeroInTheFlexFormCountsAsEmpty(): void
    {
        $result = $this->subject->override(
            ['gtag' => '0'],
            ['settings' => ['overrideFlexformSettingsIfEmpty' => 'gtag', 'gtag' => '1']],
        );

        self::assertSame('1', $result['gtag']);
    }

    #[Test]
    public function missingFlexFormValueIsTakenFromTypoScript(): void
    {
        $result = $this->subject->override(
            [],
            ['settings' => ['overrideFlexformSettingsIfEmpty' => 'gtag', 'gtag' => '1']],
        );

        self::assertSame('1', $result['gtag']);
    }

    #[Test]
    public function filledFlexFormValueIsKept(): void
    {
        $result = $this->subject->override(
            ['gtag' => 'flexform'],
            ['settings' => ['overrideFlexformSettingsIfEmpty' => 'gtag', 'gtag' => 'typoscript']],
        );

        self::assertSame('flexform', $result['gtag']);
    }

    #[Test]
    public function onlyConfiguredFieldsAreOverridden(): void
    {
        $result = $this->subject->override(
            ['gtag' => '', 'other' => ''],
            ['settings' => ['overrideFlexformSettingsIfEmpty' => 'gtag', 'gtag' => '1', 'other' => '1']],
        );

        self::assertSame('', $result['other']);
    }

    #[Test]
    public function emptyNestedFlexFormValueIsTakenFromTypoScript(): void
    {
        $result = $this->subject->override(
            ['question' => ['comment' => ['email' => ['subject' => '', 'sender' => ['name' => 'Flexform']]]]],
            [
                'settings' => [
                    'overrideFlexformSettingsIfEmpty' => 'question.comment.email.subject,question.comment.email.sender.name',
                    'question' => ['comment' => ['email' => ['subject' => 'New FAQ comment', 'sender' => ['name' => 'Website']]]],
                ],
            ],
        );

        self::assertSame('New FAQ comment', $result['question']['comment']['email']['subject']);
        self::assertSame('Flexform', $result['question']['comment']['email']['sender']['name']);
    }

    #[Test]
    public function withoutOverrideConfigurationNothingChanges(): void
    {
        $base = ['gtag' => ''];

        self::assertSame($base, $this->subject->override($base, ['settings' => ['gtag' => '1']]));
    }
}
