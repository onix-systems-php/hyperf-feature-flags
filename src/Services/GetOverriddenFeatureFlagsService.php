<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Services;

use Hyperf\Contract\ConfigInterface;
use OnixSystemsPHP\HyperfCore\Service\Service;
use OnixSystemsPHP\HyperfFeatureFlags\RedisWrapper;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;

#[Service]
readonly class GetOverriddenFeatureFlagsService
{
    public function __construct(
        private RedisWrapper $redisWrapper,
        private ConfigInterface $config,
        private FeatureFlagRepository $featureFlagRepository,
    ) {}

    /**
     * Get all overridden feature flags.
     *
     * @return array
     * @throws \RedisException
     */
    public function run(): array
    {
        $result = [];
        array_map(function ($featureFlag) use (&$result) {
            return $result[$featureFlag->name] = $featureFlag->rule;
        }, (array) $this->featureFlagRepository->all(['name', 'rule']));
        array_map(function ($key) use (&$result) {
            $result[$key] = $this->redisWrapper->get($key);
        }, array_keys($this->config->get('feature_flags')));

        return array_filter($result);
    }
}
