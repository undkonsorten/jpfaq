<?php

declare(strict_types=1);

use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;
use TYPO3\CodingStandards\CsFixerConfig;

$config = CsFixerConfig::create();
$config->setParallelConfig(ParallelConfigFactory::detect());

$config->getFinder()
    ->in(['Build', 'Classes', 'Configuration', 'Tests'])
    ->exclude(['build', '.phpunit.cache'])
    ->append(array_map('realpath', glob(__DIR__ . '/../../*.php') ?: []));

return $config;
