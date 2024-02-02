<?php

namespace OnixSystemsPHP\HyperfFeatureFlags\Test\Cases\Services;

use Hyperf\Contract\ConfigInterface;
use Mockery;
use Mockery\MockInterface;
use OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag;
use OnixSystemsPHP\HyperfFeatureFlags\RedisWrapper;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;
use OnixSystemsPHP\HyperfFeatureFlags\Services\GetOverriddenFeatureFlagsService;
use OnixSystemsPHP\HyperfFeatureFlags\Test\Fixtures\FeatureFlagFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetOverriddenFeatureFlagsService::class)]
class GetOverriddenFeatureFlagTest extends TestCase
{
    private RedisWrapper $redisWrapper;
    private MockInterface|ConfigInterface $config;
    private FeatureFlagRepository $featureFlagRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->redisWrapper = Mockery::mock(RedisWrapper::class);
        $this->config = Mockery::mock(ConfigInterface::class);
        $this->featureFlagRepository = Mockery::mock(FeatureFlagRepository::class);
    }

    #[Test]
    public function that_returns_overridden_keys_from_db_and_redis_by_config_keys()
    {
        $this->featureFlagRepository
            ->shouldReceive('all', 'toArray')
            ->andReturn(FeatureFlagFixture::fromDatabase());
        $this->config
            ->shouldReceive('get')
            ->andReturns([
                'feature-flag-4' => 0,
                'feature-flag-5' => 0,
                'feature-flag-6' => 0,
            ]);
        $this->redisWrapper
            ->shouldReceive('get')
            ->times(3)
            ->andReturns(null, null, true);
        $service = $this->getService();

        $result = $service->run();

        $this->assertEquals([
            'feature-flag-1' => 'true',
            'feature-flag-2' => 'false',
            'feature-flag-3' => 'true',
            'feature-flag-6' => '1',
        ], $result);
    }

    /**
     * @return GetOverriddenFeatureFlagsService
     */
    private function getService(): GetOverriddenFeatureFlagsService
    {
        return new GetOverriddenFeatureFlagsService($this->redisWrapper, $this->config, $this->featureFlagRepository);
    }
}
