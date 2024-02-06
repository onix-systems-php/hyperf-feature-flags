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
use Mockery\MockInterface;
use OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag;
use OnixSystemsPHP\HyperfFeatureFlags\RedisWrapper;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;
use OnixSystemsPHP\HyperfFeatureFlags\Services\GetFeatureFlagService;
use OnixSystemsPHP\HyperfFeatureFlags\Test\Fixtures\FeatureFlagFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetFeatureFlagService::class)]
class GetFeatureFlagTest extends TestCase
{
    private FeatureFlag $featureFlag;
    private MockInterface|RedisWrapper $redisWrapper;
    private MockInterface|FeatureFlagRepository $featureFlagRepository;
    private MockInterface|ConfigInterface $config;

    protected function setUp(): void
    {
        parent::setUp();
        $this->featureFlag = FeatureFlagFixture::overridden();
        $this->redisWrapper = Mockery::mock(RedisWrapper::class);
        $this->featureFlagRepository = Mockery::mock(FeatureFlagRepository::class);
        $this->config = Mockery::mock(ConfigInterface::class);
    }

    #[Test]
    public function it_returns_feature_flag_value_if_it_stored_in_redis()
    {
        $this->redisWrapper->shouldReceive('get')->andReturn('');
        $service = $this->getService(
            $this->redisWrapper,
            $this->featureFlagRepository,
            $this->config
        );

        $result = $service->run($this->featureFlag->name);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_returns_feature_flag_value_if_it_stored_in_db_and_save_it_to_redis()
    {
        $this->redisWrapper->shouldReceive('get')->andReturn(null);
        $this->redisWrapper->shouldReceive('set')->with(
            GetFeatureFlagService::FEATURE_FLAG_DOT_PREFIX . $this->featureFlag->name,
            $this->featureFlag->rule
        );
        $this->featureFlagRepository->shouldReceive('getByName')->andReturn($this->featureFlag);
        $service = $this->getService($this->redisWrapper, $this->featureFlagRepository, $this->config);

        $result = $service->run($this->featureFlag->name);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_returns_feature_flag_value_from_config_and_save_it_to_redis()
    {
        $this->redisWrapper->shouldReceive('get')->andReturn(null);
        $this->redisWrapper->shouldReceive('set')->with(
            GetFeatureFlagService::FEATURE_FLAG_DOT_PREFIX . $this->featureFlag->name,
            $this->featureFlag->rule
        );
        $this->featureFlagRepository->shouldReceive('getByName')->andReturn(null);
        $this->config->shouldReceive('get')->andReturn($this->featureFlag->rule);
        $service = $this->getService($this->redisWrapper, $this->featureFlagRepository, $this->config);

        $result = $service->run($this->featureFlag->name);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_returns_false_and_save_it_to_redis()
    {
        $this->redisWrapper->shouldReceive('get')->andReturn(null);
        $this->redisWrapper->shouldReceive('set')->with(
            GetFeatureFlagService::FEATURE_FLAG_DOT_PREFIX . $this->featureFlag->name,
            false
        );
        $this->featureFlagRepository->shouldReceive('getByName')->andReturn(null);
        $this->config->shouldReceive('get')->andReturn(null);
        $service = $this->getService($this->redisWrapper, $this->featureFlagRepository, $this->config);

        $result = $service->run($this->featureFlag->name);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_evaluates_method_properly_processes_rule_with_type_of_config()
    {
        $this->featureFlag = FeatureFlagFixture::overriddenWithConfigRule();
        $this->redisWrapper->shouldReceive('get')->andReturn(null);
        $this->redisWrapper->shouldReceive('set')->with(
            GetFeatureFlagService::FEATURE_FLAG_DOT_PREFIX . $this->featureFlag->name,
            true,
        );
        $this->featureFlagRepository->shouldReceive('getByName')->andReturn($this->featureFlag);
        $this->config->shouldReceive('get')->andReturn(true);
        $service = $this->getService($this->redisWrapper, $this->featureFlagRepository, $this->config);

        $result = $service->run($this->featureFlag->name);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_evaluates_method_properly_processes_rule_with_type_of_feature()
    {
        $this->featureFlag = FeatureFlagFixture::overriddenWithFeatureRule();
        $this->redisWrapper->shouldReceive('get')->twice()->andReturn(null, 'true');
        $this->redisWrapper->shouldReceive('set')->with(
            GetFeatureFlagService::FEATURE_FLAG_DOT_PREFIX . $this->featureFlag->name,
            true,
        );
        $this->featureFlagRepository->shouldReceive('getByName')->andReturn($this->featureFlag);
        $service = $this->getService($this->redisWrapper, $this->featureFlagRepository, $this->config);

        $result = $service->run($this->featureFlag->name);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_evaluates_method_properly_processes_rule_with_type_of_date()
    {
        $this->featureFlag = FeatureFlagFixture::overriddenWithDateRule();
        $this->redisWrapper->shouldReceive('get')->andReturn(null);
        $this->redisWrapper->shouldReceive('set')->with(
            GetFeatureFlagService::FEATURE_FLAG_DOT_PREFIX . $this->featureFlag->name,
            false,
        );
        $this->featureFlagRepository->shouldReceive('getByName')->andReturn($this->featureFlag);
        $service = $this->getService($this->redisWrapper, $this->featureFlagRepository, $this->config);

        $result = $service->run($this->featureFlag->name);

        $this->assertFalse($result);
    }

    /**
     * @param MockInterface|RedisWrapper $redisWrapper
     * @param MockInterface|FeatureFlagRepository $repository
     * @param MockInterface|ConfigInterface $config
     * @return GetFeatureFlagService
     */
    private function getService(
        MockInterface|RedisWrapper $redisWrapper,
        MockInterface|FeatureFlagRepository $repository,
        MockInterface|ConfigInterface $config
    ): GetFeatureFlagService {
        return new GetFeatureFlagService(
            $redisWrapper,
            $config,
            $repository,
        );
    }
}
