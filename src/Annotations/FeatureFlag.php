<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Annotations;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target ({"METHOD"})
 * @property string $name
 * @property bool $default
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class FeatureFlag extends AbstractAnnotation
{
    public function __construct(public string $name, public mixed $default = null) {}
}
