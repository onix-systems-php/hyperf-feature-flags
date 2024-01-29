<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Services;

use Hyperf\Database\Model\Collection;
use Hyperf\Redis\Redis;

use OnixSystemsPHP\HyperfCore\Service\Service;

use function Hyperf\Config\config;

#[Service]
readonly class GetOverriddenFeatureFlagsService
{
    public function __construct(private Redis $redis) {}

    /**
     * Get overridden feature flags.
     *
     * @return Collection|array
     * @throws \RedisException
     */
    public function run(): Collection|array
    {
        $prefix = config('redis.default.options');
        $rawKeys = $this->redis->keys('*');
        if ($prefix) {
            $keys = array_map(function ($key) use ($prefix) {
                return str_replace($prefix, '', $key);
            }, $rawKeys);
        } else {
            $keys = $rawKeys;
        }
        $values = array_map(function ($key) {
            return $this->redis->get($key);
        }, $keys);

        return array_combine($keys, $values);
    }
}
