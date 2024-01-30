<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Services;

use OnixSystemsPHP\HyperfCore\Service\Service;

use OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag;

use OnixSystemsPHP\HyperfFeatureFlags\RedisWrapper;

use function Hyperf\Config\config;

#[Service]
readonly class GetOverriddenFeatureFlagsService
{
    public function __construct(private RedisWrapper $redisWrapper) {}

    /**
     * Get all overridden feature flags.
     *
     * @return array
     * @throws \RedisException
     */
    public function run(): array
    {
        $keys = array_keys(config('feature_flags'));
        $result = [];
        array_map(function ($featureFlag) use (&$result) {
            return $result[$featureFlag['name']] = $featureFlag['rule'];
        }, FeatureFlag::all(['name', 'rule'])->toArray());
        array_map(function ($key) use (&$result) {
            $result[$key] = $this->redisWrapper->get($key);
        }, $keys);

        return array_filter($result, fn($elem) => $elem !== null);
    }
}
