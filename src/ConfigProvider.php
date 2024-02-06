<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'commands' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'migration_feature_flags',
                    'description' => 'The addition for migration from onix-systems-php/hyperf-feature-flags.',
                    'source' => __DIR__ . '/../publish/migrations/2024_01_23_081257_create_feature_flags_table.php',
                    'destination' => BASE_PATH . '/migrations/2024_01_23_081257_create_feature_flags_table.php',
                ],
                [
                    'id' => 'feature_flag_config',
                    'description' => 'The config for onix-systems-php/hyperf-feature-flags.',
                    'source' => __DIR__ . '/../publish/config/feature_flags.php',
                    'destination' => BASE_PATH . '/config/autoload/feature_flags.php',
                ],
            ],
        ];
    }
}
