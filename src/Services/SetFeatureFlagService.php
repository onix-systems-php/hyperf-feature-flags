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
    public function run(UpdateFeatureFlagDTO $featureFlagDTO): ?FeatureFlag
    {
        if (!empty($this->authenticatableProvider->user())) {
            $this->validate($featureFlagDTO);
            $featureFlag = $this->featureFlagRepository->getById($featureFlagDTO->featureFlagId);
            $this->policyGuard?->check('update', $featureFlag);
            if (is_null($featureFlagDTO->value)) {
                $featureFlag->delete();

                return null;
            }
            $featureFlag->overridden = $featureFlagDTO->value;
            $featureFlag->user_id = $this->authenticatableProvider->user()->getId();
            $featureFlag->overridden_at = Carbon::now();
            $featureFlag->save();
            $this->eventDispatcher->dispatch(
                new Action(Actions::OVERRIDE_FEATURE_FLAG, $featureFlag, [$featureFlagDTO->value])
            );

            $this->redis->del($featureFlag->feature);

            return $featureFlag;
        }
        return null;
    }

    /**
     * @param UpdateFeatureFlagDTO $featureFlagDTO
     * @return void
     */
    private function validate(UpdateFeatureFlagDTO $featureFlagDTO): void
    {
        $this->validatorFactory->make($featureFlagDTO->toArray(), [
            'featureFlagId' => ['required', 'integer'],
            'value' => ['required'],
        ], [
            'featureFlag.required' => 'Feature flag id must be required!',
            'featureFlag.integer' => 'Feature flag id must be integer!',
            'value.required' => 'Value must be required!',
        ])->validate();
    }
}