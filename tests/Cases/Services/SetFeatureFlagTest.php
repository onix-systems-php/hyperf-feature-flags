<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license https://github.com/hyperf/blob/master/LICENSE
 */

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
    private RedisWrapper $redisWrapper;
    private CorePolicyGuard $policyGuard;
    private EventDispatcher $eventDispatcher;
    private CoreAuthenticatableProvider $coreAuthenticatableProvider;
    private FeatureFlagRepository $featureFlagRepository;
    private ValidatorFactoryInterface $validatorFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->featureFlag = FeatureFlag::make(FeatureFlagFixture::successfulFeatureFlagDTO()->toArray());
        $this->featureFlagRepository = Mockery::mock(FeatureFlagRepository::class);
        $this->coreAuthenticatableProvider = Mockery::mock(CoreAuthenticatableProvider::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcher::class);
        $this->redisWrapper = Mockery::mock(RedisWrapper::class);
        $this->policyGuard = Mockery::mock(CorePolicyGuard::class);
        $this->validatorFactory = make(ValidatorFactoryInterface::class);
    }

    #[Test]
    public function that_feature_flag_stored_correctly()
    {
        $service = $this->getService();

        $featureFlag = $service->run(FeatureFlagFixture::successfulFeatureFlagDTO());

        $this->assertSame($featureFlag->name, FeatureFlagFixture::successfulFeatureFlagDTO()->name);
        $this->assertSame($featureFlag->rule, FeatureFlagFixture::successfulFeatureFlagDTO()->rule);
        $this->assertNull($this->redisWrapper->get($featureFlag->name));
    }

    #[Test]
    public function that_feature_flag_was_not_stored_because_of_invalid_data()
    {
        $this->expectException(ValidationException::class);

        $service = $this->getService();

        $service->run(FeatureFlagFixture::invalidFeatureFlagDTO());
    }

    /**
     * @return SetFeatureFlagService
     */
    private function getService(): SetFeatureFlagService
    {
        $this->featureFlagRepository
            ->shouldReceive('create', 'updateOrCreate')
            ->andReturn($this->featureFlag);
        $this->coreAuthenticatableProvider
            ->shouldReceive('user');
        $this->eventDispatcher
            ->shouldReceive('dispatch');
        $this->redisWrapper
            ->shouldReceive('get');
        $this->redisWrapper
            ->shouldReceive('del')
            ->andReturn(1);
        $this->policyGuard
            ->shouldReceive('check');

        return new SetFeatureFlagService(
            $this->coreAuthenticatableProvider,
            $this->featureFlagRepository,
            $this->eventDispatcher,
            $this->validatorFactory,
            $this->redisWrapper,
            $this->policyGuard
        );
    }
}
