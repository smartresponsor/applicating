<?php

return [
    [
        'name' => 'missing_application',
        'group' => 'identity',
        'slug' => 'non-existing',
        'expected' => [
            'canPublish' => false,
            'blockingReasons' => ['Application not found.'],
        ],
    ],
    [
        'name' => 'missing_manifest_candidate',
        'group' => 'publish',
        'slug' => 'missing-manifest-candidate',
        'expected' => [
            'canPublish' => false,
            'blockingReasons' => ['Application not found.'],
        ],
    ],
    [
        'name' => 'governance_candidate',
        'group' => 'governance',
        'slug' => 'governance-candidate',
        'expected' => [
            'canPublish' => false,
            'blockingReasons' => ['Application not found.'],
        ],
    ],
];
