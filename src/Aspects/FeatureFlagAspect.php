<?php

declare(strict_types=1);
/**
 * This file is part of the extension library for Hyperf.
 *
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace OnixSystemsPHP\HyperfFeatureFlags\Aspects;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\Di\Exception\Exception;
use OnixSystemsPHP\HyperfFeatureFlags\Annotations\FeatureFlag;
use OnixSystemsPHP\HyperfFeatureFlags\Services\GetFeatureFlagService;

#[Aspect]
class FeatureFlagAspect extends AbstractAspect
{
    public array $annotations = [
        FeatureFlag::class,
    ];

    public function __construct(private readonly GetFeatureFlagService $getFeatureFlagService)
    {
    }

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws AnnotationException
     * @throws Exception
     * @throws \RedisException
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        $featureFlagAnnotation = $this->getFeatureFlagAnnotation(
            $proceedingJoinPoint->className,
            $proceedingJoinPoint->methodName
        );
        if (!$this->getFeatureFlagService->run($featureFlagAnnotation->name)) {
            return $featureFlagAnnotation->default;
        }

        return $proceedingJoinPoint->process();
    }

    /**
     * @param string $className
     * @param string $methodName
     * @return FeatureFlag
     * @throws AnnotationException
     */
    protected function getFeatureFlagAnnotation(string $className, string $methodName): FeatureFlag
    {
        $annotation = AnnotationCollector::getClassMethodAnnotation(
            $className,
            $methodName
        )[FeatureFlag::class] ?? null;
        if (!$annotation instanceof FeatureFlag) {
            throw new AnnotationException(
                __('Invalid annotation: Expected FeatureFlag, but received ' . get_class($annotation))
            );
        }

        return $annotation;
    }
}