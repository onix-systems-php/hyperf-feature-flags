<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

#[Constants]
class RedisValue extends  AbstractConstants
{
    public const TRUE = '1';
    public const FALSE = '';
}