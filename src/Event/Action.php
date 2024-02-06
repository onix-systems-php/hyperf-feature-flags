<?php

namespace OnixSystemsPHP\HyperfFeatureFlags\Event;

use Hyperf\Database\Model\Model;

class Action
{
    public function __construct(
        public string $action,
        public ?Model $subject = null,
        public array $data = [],
    ) {}
}
