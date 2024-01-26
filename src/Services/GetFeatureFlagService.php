<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Services;

use Carbon\Carbon;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\Redis;

use OnixSystemsPHP\HyperfCore\Service\Service;

use OnixSystemsPHP\HyperfFeatureFlags\Constants\RedisValue;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;

use function Hyperf\Support\env;

#[Service]
readonly class GetFeatureFlagService
{
    public function __construct(
        private Redis $redis,
        private ConfigInterface $config,
        private FeatureFlagRepository $featureFlagRepository,
    ) {
    }

    /**
     * Check if the feature is enabled
     *
     * @param string $name
     * @return bool|null
     * @throws \RedisException
     * @throws \Exception
     */
    public function run(string $name): ?bool
    {
        if (!$this->config->get('feature_flags.enabled')) {
            return null;
        }
        $redisValue = $this->redis->get($name);
        if ($redisValue || $redisValue === '') {
            $rule = match ($redisValue) {
                RedisValue::TRUE, RedisValue::FALSE => (bool)$redisValue,
                default => $redisValue,
            };
        } elseif ($featureFlag = $this->featureFlagRepository->getByFeature($name)) {
            $rule = $featureFlag->rule;
            $this->redis->set($name, $rule);
        } else {
            $rule = $this->config->get('feature_flags.flags.' . $name) ?: false;
            $this->redis->set($name, $rule);
        }

        return $this->evaluate($rule);
    }

    /**
     * Evaluate the rule.
     *
     * @param string|bool $rule
     * @return bool
     */
    private function evaluate(string|bool $rule): bool
    {
        if (is_bool($rule)) {
            return $rule;
        }

        $values = [];
        foreach ($this->getTypeCallbacks() as $type => $callback) {
            $this->substitute($values, $rule, $type, $callback);
        }
        try {
            return eval('return ' . $rule . ';');
        } catch (\ParseError $e) {
            return false;
        }
    }

    /**
     * Substitute the rules with type and callback.
     *
     * @param array $values
     * @param string $rule
     * @param string $type
     * @param callable|null $callback
     * @return void
     */
    private function substitute(array &$values, string &$rule, string $type, ?callable $callback = null): void
    {
        preg_match_all('/\[' . $type . ':(.+?)]/', $rule, $matches);
        if (!empty($matches[0]) && !empty($matches[1]) && count($matches[0]) === count($matches[1])) {
            foreach ($matches[1] as $index => $value) {
                $values[] = !empty($callback) ? $callback($value) : false;
                $rule = str_replace($matches[0][$index], '$values[' . (count($values) - 1) . ']', $rule);
            }
        }
    }

    /**
     * Get type and its callback
     *
     * @return \Closure[]
     */
    private function getTypeCallbacks(): array
    {
        return [
            'env' => fn($value) => env($value),
            'config' => fn($value) => $this->config->get($value),
            'feature' => fn($value) => $this->run($value),
            'date' => fn($value) => (new Carbon($value))->format('Y-m-d'),
        ];
    }
}