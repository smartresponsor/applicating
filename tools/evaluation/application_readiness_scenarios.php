<?php

return [
    [
        'name' => 'missing_application',
        'group' => 'identity',
        'weight' => 1,
        'slug' => 'non-existing',
        'expected' => [
            'canPublish' => false,
            'blockingReasons' => ['Application not found.'],
        ],
    ],
    [
        'name' => 'missing_manifest_candidate',
        'group' => 'publish',
        'weight' => 2,
        'slug' => 'missing-manifest-candidate',
        'expected' => [
            'canPublish' => false,
            'blockingReasons' => ['Application not found.'],
        ],
    ],
    [
        'name' => 'governance_candidate',
        'group' => 'governance',
        'weight' => 3,
        'slug' => 'governance-candidate',
        'expected' => [
            'canPublish' => false,
            'blockingReasons' => ['Application not found.'],
        ],
    ],
];
