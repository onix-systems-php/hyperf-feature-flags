<?php

namespace OnixSystemsPHP\HyperfFeatureFlags\Test\Cases\Services;

use DummyInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Mockery;
use OnixSystemsPHP\HyperfCore\Contract\CorePolicyGuard;
use OnixSystemsPHP\HyperfFeatureFlags\RedisWrapper;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;
use OnixSystemsPHP\HyperfFeatureFlags\Services\ResetFeatureFlagService;
use OnixSystemsPHP\HyperfFeatureFlags\Test\Fixtures\ResetFeatureFlagFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Support\make;

#[CoversClass(ResetFeatureFlagService::class)]
class ResetFeatureFlagServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function that_feature_flag_resets_correctly()
    {
        $service = $this->getService();

        $result = $service->run(ResetFeatureFlagFixture::resetFeatureFlagDTO());

        $this->assertTrue($result);
    }

    #[Test]
    public function that_feature_flag_does_not_resets_because_invalid_data()
    {
        $this->expectException(ValidationException::class);

        $service = $this->getService();

        $service->run(ResetFeatureFlagFixture::invalidFeatureFlagDTO());
    }

    /**
     * @return ResetFeatureFlagService
     */
    private function getService(): ResetFeatureFlagService
    {
        $featureFlagRepository = Mockery::mock(FeatureFlagRepository::class);
        $policyGuard = Mockery::mock(CorePolicyGuard::class);
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $redisWrapper = Mockery::mock(RedisWrapper::class);
        $featureFlagRepository
            ->shouldReceive('getByName')
            ->andReturn(ResetFeatureFlagFixture::featureFlag());
        $featureFlagRepository
            ->shouldReceive('delete');
        $policyGuard
            ->shouldReceive('check');
        $eventDispatcher
            ->shouldReceive('dispatch');
        $redisWrapper
            ->shouldReceive('del');
        return new ResetFeatureFlagService(
            make(ValidatorFactoryInterface::class),
            $featureFlagRepository,
            $policyGuard,
            $eventDispatcher,
            $redisWrapper
        );
    }
}
