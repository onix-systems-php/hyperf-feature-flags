<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license https://github.com/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Test\Cases\Services;

use Hyperf\Contract\ConfigInterface;
use Mockery;
use OnixSystemsPHP\HyperfFeatureFlags\Services\GetCurrentFeatureFlagsService;
use OnixSystemsPHP\HyperfFeatureFlags\Services\GetFeatureFlagService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetCurrentFeatureFlagsService::class)]
class GetCurrentFeatureFlagsTest extends TestCase
{
    private GetFeatureFlagService $getFeatureFlagService;
    private ConfigInterface $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = Mockery::mock(ConfigInterface::class);
        $this->getFeatureFlagService = Mockery::mock(GetFeatureFlagService::class);
    }

    #[Test]
    public function that_service_returns_all_current_feature_flags()
    {
        $service = $this->getService();

        $result = $service->run();

        $this->assertEquals([
            'feature-flag-1' => true,
            'feature-flag-2' => true,
            'feature-flag-3' => true,
        ], $result);
    }

    /**
     * @return GetCurrentFeatureFlagsService
     */
    private function getService(): GetCurrentFeatureFlagsService
    {
        $this->getFeatureFlagService
            ->shouldReceive('run')
            ->times(3)
            ->andReturn(true, true, true);
        $this->config
            ->shouldReceive('get')
            ->with('feature_flags')
            ->andReturn([
                'feature-flag-1' => true,
                'feature-flag-2' => true,
                'feature-flag-3' => true,
            ]);

        return new GetCurrentFeatureFlagsService($this->getFeatureFlagService, $this->config);
    }
}
