<?php

namespace OnixSystemsPHP\HyperfFeatureFlags\Test\Cases\Services;

use Hyperf\Event\EventDispatcher;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Mockery;
use OnixSystemsPHP\HyperfCore\Contract\CoreAuthenticatableProvider;
use OnixSystemsPHP\HyperfCore\Contract\CorePolicyGuard;
use OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag;
use OnixSystemsPHP\HyperfFeatureFlags\RedisWrapper;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;
use OnixSystemsPHP\HyperfFeatureFlags\Services\SetFeatureFlagService;
use OnixSystemsPHP\HyperfFeatureFlags\Test\Fixtures\FeatureFlagFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function Hyperf\Support\make;

#[CoversClass(SetFeatureFlagService::class)]
class SetFeatureFlagTest extends TestCase
{
    private FeatureFlag $featureFlag;

    #[Test]
    public function that_feature_flag_stored_correctly()
    {
        $service = $this->getService();
        $featureFlag = $service->run(FeatureFlagFixture::successfulFeatureFlagDTO());
        $this->assertSame($featureFlag->name, FeatureFlagFixture::successfulFeatureFlagDTO()->name);
        $this->assertSame($featureFlag->rule, FeatureFlagFixture::successfulFeatureFlagDTO()->rule);
        $this->assertNull($this->redisWrapper->get($featureFlag->name));
    }

    /**
     * @return SetFeatureFlagService
     */
    private function getService(): SetFeatureFlagService
    {
        $featureFlagRepository = Mockery::mock(FeatureFlagRepository::class);
        $featureFlagRepository->shouldReceive('create', 'updateOrCreate')->andReturn($this->featureFlag);
        $coreAuthenticatableProvider = Mockery::mock(CoreAuthenticatableProvider::class);
        $coreAuthenticatableProvider->shouldReceive('user');
        $eventDispatcher = Mockery::mock(EventDispatcher::class);
        $eventDispatcher->shouldReceive('dispatch');
        $redisWrapper = Mockery::mock(RedisWrapper::class);
        $redisWrapper->shouldReceive('del')->andReturn(1);
        $policyGuard = Mockery::mock(CorePolicyGuard::class);
        $policyGuard->shouldReceive('check');

        return new SetFeatureFlagService(
            $coreAuthenticatableProvider,
            $featureFlagRepository,
            $eventDispatcher,
            make(ValidatorFactoryInterface::class),
            $redisWrapper,
            $policyGuard
        );
    }

    #[Test]
    public function that_feature_flag_was_not_stored_because_of_invalid_data()
    {
        $this->expectException(ValidationException::class);

        $service = $this->getService();

        $service->run(FeatureFlagFixture::invalidFeatureFlagDTO());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->featureFlag = FeatureFlag::make(FeatureFlagFixture::successfulFeatureFlagDTO()->toArray());
        $this->redisWrapper = make(RedisWrapper::class);
    }
}
