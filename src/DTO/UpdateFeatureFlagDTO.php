<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\DTO;

use OnixSystemsPHP\HyperfCore\DTO\AbstractDTO;

class UpdateFeatureFlagDTO extends AbstractDTO
{
    public int $featureFlagId;
    public ?bool $value;
}