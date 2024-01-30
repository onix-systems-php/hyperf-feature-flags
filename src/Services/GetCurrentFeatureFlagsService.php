<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Services;

use OnixSystemsPHP\HyperfCore\Service\Service;

use function Hyperf\Config\config;

#[Service]
readonly class GetCurrentFeatureFlagsService
{
    public function __construct(private GetFeatureFlagService $getFeatureFlagService) {}

    /**
     * Get feature flags from config and their values.
     *
     * @return array
     * @throws \RedisException
     */
    public function run(): array
    {
        $result = [];
        $keys = array_keys(config('feature_flags'));
        array_map(function ($key) use (&$result) {
            return $result[$key] = $this->getFeatureFlagService->run($key);
        }, $keys);

        return $result;
    }
}
