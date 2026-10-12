<?php

declare(strict_types=1);

namespace Jp\Jpfaq\Tests\Functional\Frontend;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Renders the FAQ plugin in a real frontend request: plugin registration,
 * FlexForm settings, Extbase controller and the Fluid templates.
 */
#[CoversNothing]
final class FaqPluginRenderingTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['jvdp/jpfaq'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/Questions.csv');
        $this->importCSVDataSet(__DIR__ . '/../Fixtures/FaqPlugin.csv');
        $this->writeSiteConfiguration();
        $this->setUpFrontendRootPage(1, [
            'constants' => ['EXT:jpfaq/Configuration/TypoScript/constants.typoscript'],
            'setup' => [
                'EXT:jpfaq/Configuration/TypoScript/setup.typoscript',
                'EXT:jpfaq/Tests/Functional/Fixtures/TypoScript/Page.typoscript',
            ],
        ]);
    }

    #[Test]
    public function rendersTheVisibleQuestionsOfTheStorageFolder(): void
    {
        $body = $this->renderFaqPage();

        self::assertStringContainsString('How do I install it?', $body);
        self::assertStringContainsString('<p>With composer.</p>', $body);
        self::assertStringContainsString('How do I configure it?', $body);
        self::assertStringContainsString('Is it free?', $body);
    }

    #[Test]
    public function leavesOutQuestionsOfOtherStorageFolders(): void
    {
        self::assertStringNotContainsString('Where is the installation guide?', $this->renderFaqPage());
    }

    #[Test]
    public function leavesOutHiddenQuestions(): void
    {
        self::assertStringNotContainsString('Hidden question', $this->renderFaqPage());
    }

    #[Test]
    public function rendersTheSearchFormWhenEnabledInTheFlexForm(): void
    {
        self::assertStringContainsString('jpfaqSearch', $this->renderFaqPage());
    }

    private function renderFaqPage(): string
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('https://example.com/'));

        self::assertSame(200, $response->getStatusCode());
        return (string)$response->getBody();
    }

    private function writeSiteConfiguration(): void
    {
        $path = $this->instancePath . '/typo3conf/sites/main';
        @mkdir($path, 0777, true);
        file_put_contents(
            $path . '/config.yaml',
            implode("\n", [
                'rootPageId: 1',
                "base: 'https://example.com/'",
                'languages:',
                '  - languageId: 0',
                "    title: English",
                "    locale: en_US.UTF-8",
                "    base: '/'",
                '',
            ])
        );
    }
}
