<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Direct Content',
    'description' => 'Adds special doktype to edit a single content element directly in page view.',
    'category' => 'content backend frontend page',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Sethorax',
    'author_email' => 'info@sethorax.com',
    'version' => '0.1.1',
    'constraints' => [
        'depends' => [
            'typo3' => '9.2.0-9.9.99',
        ]
    ]
];
