<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Model;

use Carbon\Carbon;
use OnixSystemsPHP\HyperfCore\Model\AbstractOwnedModel;

/**
 * @property int $id
 * @property string $feature
 * @property string $rule
 * @property int|null $user_id
 * @property bool|null $overridden
 * @property Carbon|null $overridden_at
 */
class FeatureFlag extends AbstractOwnedModel
{
    public bool $timestamps = false;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'feature_flags';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'feature',
        'rule',
        'overridden_at',
        'user_id',
        'overridden',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'id' => 'integer',
        'feature' => 'string',
        'rule' => 'string',
        'overridden_at' => 'datetime',
        'user_id' => 'integer',
        'overridden' => 'boolean',
    ];
}
