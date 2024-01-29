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
    public function __construct(private GetOverriddenFeatureFlagsService $overriddenFeatureFlagsService) {}

    /**
     * Get current feature flags.
     *
     * @return array|mixed
     * @throws \RedisException
     */
    public function run(): mixed
    {
        $overridden = $this->overriddenFeatureFlagsService->run();
        $fromConfig = config('feature_flags');

        return $overridden + $fromConfig;
    }
}
