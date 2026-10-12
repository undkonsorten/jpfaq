<?php

declare(strict_types=1);

namespace Jp\Jpfaq\Tests\Functional\Domain\Repository;

use Jp\Jpfaq\Domain\Model\Question;
use Jp\Jpfaq\Domain\Repository\QuestionRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(QuestionRepository::class)]
final class QuestionRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['jvdp/jpfaq'];

    private QuestionRepository $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Questions.csv');
        $typoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoScript->setSetupArray([]);
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest('https://example.com/'))
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('frontend.typoscript', $typoScript);
        unset($GLOBALS['EXT']['jpfaq']);
        $this->subject = $this->get(QuestionRepository::class);
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST'], $GLOBALS['EXT']['jpfaq']);
        parent::tearDown();
    }

    #[Test]
    public function withoutConstraintsAllVisibleQuestionsAreFoundInSortingOrder(): void
    {
        self::assertSame([1, 3, 2, 4], $this->findUids());
    }

    #[Test]
    public function hiddenQuestionsAreNotFound(): void
    {
        self::assertNotContains(5, $this->findUids());
    }

    #[Test]
    public function startingPointRestrictsToTheseStorageFolders(): void
    {
        self::assertSame([1, 2, 4], $this->findUids(startingPoint: '2'));
        self::assertSame([3], $this->findUids(startingPoint: '3'));
        self::assertSame([1, 3, 2, 4], $this->findUids(startingPoint: '2,3'));
    }

    #[Test]
    public function categoriesRestrictToQuestionsOfAnyOfThem(): void
    {
        self::assertSame([1, 3], $this->findUids(categories: [1]));
        self::assertSame([1, 3, 2], $this->findUids(categories: [1, 2]));
    }

    #[Test]
    public function alreadyDisplayedQuestionsAreExcluded(): void
    {
        $GLOBALS['EXT']['jpfaq']['alreadyDisplayed'] = [1 => 1, 4 => 4];

        self::assertSame([3, 2], $this->findUids(excludeAlreadyDisplayed: true));
    }

    #[Test]
    public function alreadyDisplayedQuestionsAreOnlyExcludedOnRequest(): void
    {
        $GLOBALS['EXT']['jpfaq']['alreadyDisplayed'] = [1 => 1, 4 => 4];

        self::assertSame([1, 3, 2, 4], $this->findUids());
    }

    #[Test]
    public function categoriesAndStartingPointMustBothMatch(): void
    {
        self::assertSame([1], $this->findUids(categories: [1], startingPoint: '2'));
    }

    #[Test]
    public function categoriesStartingPointAndExclusionMustAllMatch(): void
    {
        $GLOBALS['EXT']['jpfaq']['alreadyDisplayed'] = [1 => 1];

        self::assertSame([], $this->findUids(categories: [1], excludeAlreadyDisplayed: true, startingPoint: '2'));
    }

    /**
     * @param list<int> $categories
     * @return list<int>
     */
    private function findUids(array $categories = [], bool $excludeAlreadyDisplayed = false, ?string $startingPoint = null): array
    {
        $uids = [];
        /** @var Question $question */
        foreach ($this->subject->findQuestionsWithConstraints($categories, $excludeAlreadyDisplayed, $startingPoint) as $question) {
            $uids[] = $question->getUid();
        }
        return $uids;
    }
}
