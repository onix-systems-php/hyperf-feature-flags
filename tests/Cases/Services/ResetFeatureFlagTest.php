<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license https://github.com/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Test\Cases\Services;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;
use Mockery;
use OnixSystemsPHP\HyperfCore\Contract\CorePolicyGuard;
use OnixSystemsPHP\HyperfFeatureFlags\RedisWrapper;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;
use OnixSystemsPHP\HyperfFeatureFlags\Services\ResetFeatureFlagService;
use OnixSystemsPHP\HyperfFeatureFlags\Test\Fixtures\FeatureFlagFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

use function Hyperf\Support\make;

#[CoversClass(ResetFeatureFlagService::class)]
class ResetFeatureFlagTest extends TestCase
{
    private FeatureFlagRepository $featureFlagRepository;
    private CorePolicyGuard $policyGuard;
    private EventDispatcherInterface $eventDispatcher;
    private RedisWrapper $redisWrapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->featureFlagRepository = Mockery::mock(FeatureFlagRepository::class);
        $this->policyGuard = Mockery::mock(CorePolicyGuard::class);
        $this->eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $this->redisWrapper = Mockery::mock(RedisWrapper::class);
    }

    #[Test]
    public function that_feature_flag_was_reset_correctly()
    {
        $service = $this->getService();

        $result = $service->run(FeatureFlagFixture::resetFeatureFlagDTO());

        $this->assertTrue($result);
    }

    #[Test]
    public function that_feature_flag_was_not_reset_because_invalid_data()
    {
        $this->expectException(ValidationException::class);

        $service = $this->getService();

        $service->run(FeatureFlagFixture::invalidResetFeatureFlagDTO());
    }

    /**
     * @return ResetFeatureFlagService
     */
    private function getService(): ResetFeatureFlagService
    {
        $this->featureFlagRepository
            ->shouldReceive('getByName')
            ->andReturn(FeatureFlagFixture::overriddenWithFeatureRule());
        $this->featureFlagRepository
            ->shouldReceive('delete');
        $this->policyGuard
            ->shouldReceive('check');
        $this->eventDispatcher
            ->shouldReceive('dispatch');
        $this->redisWrapper
            ->shouldReceive('del');

        return new ResetFeatureFlagService(
            make(ValidatorFactoryInterface::class),
            $this->featureFlagRepository,
            $this->policyGuard,
            $this->eventDispatcher,
            $this->redisWrapper
        );
    }
}
