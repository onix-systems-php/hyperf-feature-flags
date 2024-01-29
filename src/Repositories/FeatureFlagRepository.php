<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Repositories;

use OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag;
use Hyperf\Database\Model\Model;
use OnixSystemsPHP\HyperfCore\Model\Builder;
use OnixSystemsPHP\HyperfCore\Repository\AbstractRepository;

/**
 * @method \OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag create(array $data = [])
 * @method \OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag update(Model $model, array $data)
 * @method \OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag save(Model $model)
 * @method \OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag delete(Model $model)
 * @method \OnixSystemsPHP\HyperfCore\Model\Builder|\OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository finder(string $type, ...$parameters)
 * @method \OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag|null fetchOne(bool $lock, bool $force)
 */
class FeatureFlagRepository extends AbstractRepository
{
    protected string $modelClass = FeatureFlag::class;

    /**
     * @param string $name
     * @param bool $lock
     * @param bool $force
     * @return FeatureFlag|null
     */
    public function getByName(string $name, bool $lock = false, bool $force = false): ?FeatureFlag
    {
        return $this->finder('name', $name)->fetchOne($lock, $force);
    }

    /**
     * @param Builder $query
     * @param string $name
     * @return void
     */
    public function scopeName(Builder $query, string $name): void
    {
        $query->where('name', $name);
    }
}
