<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Services;

use Hyperf\Database\Model\ModelNotFoundException;
use Hyperf\DbConnection\Annotation\Transactional;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use OnixSystemsPHP\HyperfActionsLog\Event\Action;
use OnixSystemsPHP\HyperfCore\Contract\CorePolicyGuard;
use OnixSystemsPHP\HyperfCore\Service\Service;
use OnixSystemsPHP\HyperfFeatureFlags\Constants\Actions;
use OnixSystemsPHP\HyperfFeatureFlags\DTO\ResetFeatureFlagDTO;
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
        private Redis $redis,
    ) {
    }

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

        if ($resetFeatureFlagDTO->disable) {
            $featureFlag = $this->featureFlagRepository->getById($resetFeatureFlagDTO->id);

            if (!$featureFlag) {
                throw new ModelNotFoundException('Model not found');
            }
            $this->policyGuard?->check('reset', $featureFlag);
            $featureFlag->delete();
            $this->redis->del($featureFlag->name);

            $this->eventDispatcher->dispatch(
                new Action(Actions::RESET_FEATURE_FLAG, $featureFlag, [
                    'feature_flag_id' => $resetFeatureFlagDTO->id,
                    'disable' => $resetFeatureFlagDTO->disable,
                ])
            );
        }
    }

    /**
     * @param ResetFeatureFlagDTO $featureFlagDTO
     * @return void
     */
    public function validate(ResetFeatureFlagDTO $featureFlagDTO): void
    {
        $this->validatorFactory->make($featureFlagDTO->toArray(), [
            'id' => ['required'],
            'disable' => ['required', 'boolean']
        ], [
            'id.required' => 'Feature flag id must be required!',
            'disable.required' => 'Disable must be required!',
            'disable.boolean' => 'Disable should take true or false!'
        ])->validate();
    }
}
