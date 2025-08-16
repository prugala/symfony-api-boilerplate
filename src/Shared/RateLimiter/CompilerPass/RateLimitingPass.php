<?php

declare(strict_types=1);

namespace App\Shared\RateLimiter\CompilerPass;

use App\Shared\RateLimiter\Attribute\RateLimiting;
use App\Shared\RateLimiter\EventListener\ApplyRateLimitingListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

final readonly class RateLimitingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('controller.service_arguments');

        /** @var Definition[] $serviceDefinitions */
        $serviceDefinitions = array_map(fn (string $id) => $container->getDefinition($id), array_keys($taggedServices));

        $rateLimiterClassMap = [];

        foreach ($serviceDefinitions as $serviceDefinition) {
            $controllerClass = $serviceDefinition->getClass();
            $reflectionClass = $container->getReflectionClass($controllerClass);

            $attributes = $reflectionClass?->getAttributes(RateLimiting::class) ?? [];

            if (\count($attributes) > 0) {
                [$attribute] = $attributes;

                $serviceKey = sprintf('limiter.%s', $attribute->newInstance()->configuration);
                $rateLimiterClassMap[$reflectionClass->getName()] = $container->getDefinition($serviceKey);
            }
        }

        $container->getDefinition(ApplyRateLimitingListener::class)->setArgument('$rateLimiterClassMap', $rateLimiterClassMap);
    }
}
