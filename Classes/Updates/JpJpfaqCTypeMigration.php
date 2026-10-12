<?php

declare(strict_types=1);

namespace Jp\Jpfaq\Updates;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('jpJpfaqCTypeMigration')]
final class JpJpfaqCTypeMigration extends AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Migrate jpFAQ plugins to content elements.';
    }

    public function getDescription(): string
    {
        return 'The jpFAQ plugins are now registered as content element. Update migrates existing records and backend user permissions.';
    }

    /**
     * Installations coming from TYPO3 12 still have list_type plugins
     *
     * @return array<string, string>
     */
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'jpfaq_faq' => 'jpfaq_faq',
        ];
    }
}
