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

    public function getByFeature(string $name, bool $lock = false, bool $force = false): ?FeatureFlag
    {
        return $this->finder('feature', $name)->fetchOne($lock, $force);
    }

    public function scopeFeature(Builder $query, string $name): void
    {
        $query->where('feature', $name);
    }

    public function getById(int $id, bool $lock = false, bool $force = false): ?FeatureFlag
    {
        return $this->finder('id', $id)->fetchOne($lock, $force);
    }

    public function scopeId(Builder $query, int $id): void
    {
        $query->where('id', $id);
    }
}