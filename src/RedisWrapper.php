<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags;

use Hyperf\Redis\Redis;

class RedisWrapper extends Redis
{
    /**
     * @inheritDoc
     */
    public function get($key): null|string
    {
        $value = parent::get($key);

        return is_string($value) ? $value : null;
    }
}
