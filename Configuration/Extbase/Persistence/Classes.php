<?php

declare(strict_types=1);

use Jp\Jpfaq\Domain\Model\TtContent;

return [
    TtContent::class => [
        'tableName' => 'tt_content',
        'properties' => [
            'contentType' => [
                'fieldName' => 'CType',
            ],
        ],
    ],
];
