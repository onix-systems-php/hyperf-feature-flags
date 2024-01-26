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
class Actions extends AbstractConstants
{
    public const OVERRIDE_FEATURE_FLAG = 'override-feature-flag';
}