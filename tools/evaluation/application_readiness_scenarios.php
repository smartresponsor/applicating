<?php

return [
    [
        'name' => 'missing_application',
        'slug' => 'non-existing',
        'expected' => [
            'canPublish' => false,
            'blockingReasons' => ['Application not found.'],
        ],
    ],
];
