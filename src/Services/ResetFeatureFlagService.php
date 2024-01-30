<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Services;

use Hyperf\DbConnection\Annotation\Transactional;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use OnixSystemsPHP\HyperfActionsLog\Event\Action;
use OnixSystemsPHP\HyperfCore\Contract\CorePolicyGuard;
use OnixSystemsPHP\HyperfCore\Service\Service;
use OnixSystemsPHP\HyperfFeatureFlags\Constants\Actions;
use OnixSystemsPHP\HyperfFeatureFlags\DTO\ResetFeatureFlagDTO;
use OnixSystemsPHP\HyperfFeatureFlags\RedisWrapper;
use OnixSystemsPHP\HyperfFeatureFlags\Repositories\FeatureFlagRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Redis;

#[Service]
readonly class ResetFeatureFlagService
{
    public function __construct(
        private ValidatorFactoryInterface $validatorFactory,
        private FeatureFlagRepository $featureFlagRepository,
        private ?CorePolicyGuard $policyGuard,
        private EventDispatcherInterface $eventDispatcher,
        private RedisWrapper $redisWrapper,
    ) {}

    /**
     * Reset feature flag to default.
     *
     * @param ResetFeatureFlagDTO $resetFeatureFlagDTO
     * @return void
     * @throws \RedisException
     */
    #[Transactional]
    public function run(ResetFeatureFlagDTO $resetFeatureFlagDTO): void
    {
        $this->validate($resetFeatureFlagDTO);

        $featureFlag = $this->featureFlagRepository->getByName($resetFeatureFlagDTO->name, force: true);

        $this->policyGuard?->check('reset', $featureFlag);
        $featureFlag->delete();
        $this->redisWrapper->del($featureFlag->name);

        $this->eventDispatcher->dispatch(
            new Action(Actions::RESET_FEATURE_FLAG, $featureFlag, [
                'feature_flag_name' => $resetFeatureFlagDTO->name,
            ])
        );
    }

    /**
     * @param ResetFeatureFlagDTO $featureFlagDTO
     * @return void
     */
    public function validate(ResetFeatureFlagDTO $featureFlagDTO): void
    {
        $this->validatorFactory->make($featureFlagDTO->toArray(), [
            'name' => ['required'],
        ])->validate();
    }
}
