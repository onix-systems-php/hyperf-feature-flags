<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Services;

use Carbon\Carbon;
use Hyperf\Redis\Redis;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use OnixSystemsPHP\HyperfActionsLog\Event\Action;
use OnixSystemsPHP\HyperfCore\Contract\CoreAuthenticatableProvider;
use OnixSystemsPHP\HyperfCore\Contract\CorePolicyGuard;
use OnixSystemsPHP\HyperfCore\Service\Service;
use OnixSystemsPHP\HyperfFeatureFlags\Constants\Actions;
use OnixSystemsPHP\HyperfFeatureFlags\DTO\UpdateFeatureFlagDTO;
use OnixSystemsPHP\HyperfFeatureFlags\Model\FeatureFlag;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;
use Psr\EventDispatcher\EventDispatcherInterface;

#[Service]
readonly class SetFeatureFlagService
{
    public function __construct(
        private CoreAuthenticatableProvider $authenticatableProvider,
        private FeatureFlagRepository $featureFlagRepository,
        private EventDispatcherInterface $eventDispatcher,
        private ValidatorFactoryInterface $validatorFactory,
        private Redis $redis,
        private ?CorePolicyGuard $policyGuard,
    ) {
    }

    /**
     * Override the feature flag.
     *
     * @param UpdateFeatureFlagDTO $featureFlagDTO
     * @return FeatureFlag|null
     * @throws \RedisException
     * @throws \Exception
     */
    public function run(UpdateFeatureFlagDTO $updateFeatureFlagDTO): ?FeatureFlag
    {
        $this->validate($updateFeatureFlagDTO);
        /** @var FeatureFlag $featureFlag */
        $featureFlag = $this->featureFlagRepository->updateOrCreate(
            ['name' => $updateFeatureFlagDTO->name],
            [
                'rule' => $updateFeatureFlagDTO->rule,
                'overridden_at' => Carbon::now(),
                'user_id' => $this->authenticatableProvider->user()->getId()
            ],
        );
        $this->policyGuard?->check('create', $featureFlag);

        $this->eventDispatcher->dispatch(
            new Action(Actions::OVERRIDE_FEATURE_FLAG, $featureFlag, [$updateFeatureFlagDTO->rule])
        );

        $this->redis->del($updateFeatureFlagDTO->name);

        return $featureFlag;
    }

    /**
     * @param UpdateFeatureFlagDTO $updateFeatureFlagDTO
     * @return void
     */
    private function validate(UpdateFeatureFlagDTO $updateFeatureFlagDTO): void
    {
        $this->validatorFactory->make($updateFeatureFlagDTO->toArray(), [
            'name' => ['required', 'string'],
            'rule' => ['required'],
        ], [
            'name.required' => 'Name must be required!',
            'name.string' => 'Name must be string!',
            'rule.required' => 'Value must be required!',
        ])->validate();
    }
}