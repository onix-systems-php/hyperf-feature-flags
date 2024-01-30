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
use OnixSystemsPHP\HyperfCore\Service\Service;
use OnixSystemsPHP\HyperfFeatureFlags\RedisWrapper;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;

#[Service]
readonly class GetFeatureFlagService
{
    public const FEATURE_FLAG_DOT_PREFIX = 'feature_flag.';

    public function __construct(
        private RedisWrapper $redisWrapper,
        private ConfigInterface $config,
        private FeatureFlagRepository $featureFlagRepository,
    ) {}

    /**
     * Check if the feature is enabled.
     *
     * @throws \RedisException
     * @throws \Exception
     */
    public function run(string $name): ?bool
    {
        $redisValue = $this->redisWrapper->get(self::FEATURE_FLAG_DOT_PREFIX . $name);
        if (!empty($redisValue)) {
            return (bool) $redisValue;
        }
        if ($featureFlag = $this->featureFlagRepository->getByName($name)) {
            $result = $this->evaluate($featureFlag->rule);
        } else {
            $result = $this->evaluate($this->config->get('feature_flags.' . $name) ?: false);
        }
        $this->redisWrapper->set(self::FEATURE_FLAG_DOT_PREFIX . $name, $result);

        return $result;
    }

    /**
     * Evaluate the rule.
     *
     * @param bool|string $rule
     * @return bool
     */
    private function evaluate(bool|string $rule): bool
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
     * Get type and its callback.
     *
     * @return \Closure[]
     */
    private function getTypeCallbacks(): array
    {
        return [
            'config' => fn($value) => $this->config->get($value),
            'feature' => fn($value) => $this->run($value),
            'date' => fn($value) => (new Carbon($value))->format('Y-m-d'),
        ];
    }
}
