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
        $languagesPath = $this->getLanguagePath();

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
                [
                    'id' => 'en_us_translation',
                    'description' => 'The feature-flags English translation for onix-systems-php/hyperf-feature-flags.',
                    'source' => __DIR__ . '/../publish/languages/en-US/feature-flags.php',
                    'destination' => $languagesPath . '/en-US/feature-flags.php',
                ],
                [
                    'id' => 'ua_uk_translation',
                    'description' => 'The feature-flags Ukraine translation for onix-systems-php/hyperf-feature-flags.',
                    'source' => __DIR__ . '/../publish/languages/uk-UA/feature-flagse.php',
                    'destination' => $languagesPath . '/uk-UA/feature-flags.php',
                ],
            ],
        ];
    }

    private function getLanguagePath(): string
    {
        $languagesPath = BASE_PATH . '/storage/languages';
        $translationConfigFile = BASE_PATH . '/config/autoload/translation.php';
        if (file_exists($translationConfigFile)) {
            $translationConfig = include $translationConfigFile;
            $languagesPath = $translationConfig['path'] ?? $languagesPath;
        }

        return $languagesPath;
    }
}
