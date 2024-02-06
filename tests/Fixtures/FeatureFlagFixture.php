<?php

namespace OnixSystemsPHP\HyperfFeatureFlags\Test\Fixtures;

use Carbon\Carbon;
use Hyperf\Database\Model\Model;
use OnixSystemsPHP\HyperfFeatureFlags\DTO\ResetFeatureFlagDTO;
use OnixSystemsPHP\HyperfFeatureFlags\DTO\UpdateFeatureFlagDTO;
use OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag;
use Random\RandomException;

class FeatureFlagFixture
{
    /**
     * @return Model|FeatureFlag
     * @throws RandomException
     */
    public static function overridden(): Model|FeatureFlag
    {
        return FeatureFlag::make([
            'name' => 'test-feature',
            'rule' => 'true',
            'overridden_at' => Carbon::now(),
            'user_id' => random_int(1, 100),
        ]);
    }

    /**
     * @return Model|FeatureFlag
     * @throws RandomException
     */
    public static function overriddenWithConfigRule(): Model|FeatureFlag
    {
        return FeatureFlag::make([
            'name' => 'test-feature',
            'rule' => '[config:test-feature]',
            'overridden_at' => Carbon::now(),
            'user_id' => random_int(1, 100),
        ]);
    }

    /**
     * @return Model|FeatureFlag
     * @throws RandomException
     */
    public static function overriddenWithDateRule(): Model|FeatureFlag
    {
        return FeatureFlag::make([
            'name' => 'test-feature',
            'rule' => '[date:now] > "2032-02-02"',
            'overridden_at' => Carbon::now(),
            'user_id' => random_int(1, 100),
        ]);
    }

    /**
     * @return Model|FeatureFlag
     * @throws RandomException
     */
    public static function overriddenWithFeatureRule(): Model|FeatureFlag
    {
        return FeatureFlag::make([
            'name' => 'test-feature',
            'rule' => '[feature:test-feature]',
            'overridden_at' => Carbon::now(),
            'user_id' => random_int(1, 100),
        ]);
    }

    /**
     * @return UpdateFeatureFlagDTO
     */
    public static function successfulFeatureFlagDTO(): UpdateFeatureFlagDTO
    {
        return UpdateFeatureFlagDTO::make([
            'name' => 'test-feature',
            'rule' => true,
        ]);
    }

    /**
     * @return UpdateFeatureFlagDTO
     */
    public static function invalidFeatureFlagDTO(): UpdateFeatureFlagDTO
    {
        return UpdateFeatureFlagDTO::make([
            'rule' => 'test',
        ]);
    }

    public static function invalidResetFeatureFlagDTO(): ResetFeatureFlagDTO
    {
        return ResetFeatureFlagDTO::make([]);
    }

    public static function resetFeatureFlagDTO(): ResetFeatureFlagDTO
    {
        return ResetFeatureFlagDTO::make([
            'name' => 'test-feature',
        ]);
    }

    /**
     * @return array
     */
    public static function fromDatabase(): array
    {
        return [
            FeatureFlag::make(['name' => 'feature-flag-1', 'rule' => 'true']),
            FeatureFlag::make(['name' => 'feature-flag-2', 'rule' => 'false']),
            FeatureFlag::make(['name' => 'feature-flag-3', 'rule' => 'true']),
        ];
    }
}
