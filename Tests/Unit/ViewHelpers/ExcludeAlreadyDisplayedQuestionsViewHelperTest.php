<?php

declare(strict_types=1);

namespace Jp\Jpfaq\Tests\Unit\ViewHelpers;

use Jp\Jpfaq\Domain\Model\Question;
use Jp\Jpfaq\ViewHelpers\ExcludeAlreadyDisplayedQuestionsViewHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ExcludeAlreadyDisplayedQuestionsViewHelper::class)]
final class ExcludeAlreadyDisplayedQuestionsViewHelperTest extends UnitTestCase
{
    protected bool $backupGlobals = true;

    private ExcludeAlreadyDisplayedQuestionsViewHelper $subject;

    protected function setUp(): void
    {
        parent::setUp();
        unset($GLOBALS['EXT']['jpfaq']);
        $this->subject = new ExcludeAlreadyDisplayedQuestionsViewHelper();
    }

    #[Test]
    public function rendersNothing(): void
    {
        $this->subject->setArguments(['question' => $this->createQuestion(3)]);

        self::assertSame('', $this->subject->render());
    }

    #[Test]
    public function remembersTheQuestionAsDisplayed(): void
    {
        $this->subject->setArguments(['question' => $this->createQuestion(3)]);
        $this->subject->render();

        self::assertSame([3 => 3], $GLOBALS['EXT']['jpfaq']['alreadyDisplayed']);
    }

    #[Test]
    public function keepsQuestionsOfEarlierCalls(): void
    {
        $this->subject->setArguments(['question' => $this->createQuestion(3)]);
        $this->subject->render();
        $this->subject->setArguments(['question' => $this->createQuestion(5)]);
        $this->subject->render();

        self::assertSame([3 => 3, 5 => 5], $GLOBALS['EXT']['jpfaq']['alreadyDisplayed']);
    }

    #[Test]
    public function remembersTheLocalizedUidAsWell(): void
    {
        $this->subject->setArguments(['question' => $this->createQuestion(3, 7)]);
        $this->subject->render();

        self::assertSame([3 => 3, 7 => 7], $GLOBALS['EXT']['jpfaq']['alreadyDisplayed']);
    }

    private function createQuestion(int $uid, int $localizedUid = 0): Question
    {
        $question = new Question();
        $question->_setProperty('uid', $uid);
        if ($localizedUid > 0) {
            $question->_setProperty('_localizedUid', $localizedUid);
        }
        return $question;
    }
}
