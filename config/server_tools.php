<?php

return [
    'path' => env('AMOW_DEPLOY_PATH', base_path()),
    'timeout' => (int) env('AMOW_TOOLS_TIMEOUT_MS', 120000),
    'binaries' => [
        'php' => env('AMOW_PHP_BINARY', PHP_BINARY ?: 'php'),
        'git' => env('AMOW_GIT_BINARY', 'git'),
        'composer' => env('AMOW_COMPOSER_BINARY', 'composer'),
        'npm' => env('AMOW_NPM_BINARY', 'npm'),
    ],
];
