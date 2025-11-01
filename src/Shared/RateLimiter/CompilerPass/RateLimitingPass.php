<?php

declare(strict_types=1);

namespace App\Shared\RateLimiter\CompilerPass;

use App\Shared\RateLimiter\Attribute\RateLimiting;
use App\Shared\RateLimiter\Enum\Type;
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
        $serviceDefinitions = array_map($container->getDefinition(...), array_keys($taggedServices));

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

        $rateLimiterClassMap = array_merge($rateLimiterClassMap, $this->processResetPasswordEndpoints($container));

        $container->getDefinition(ApplyRateLimitingListener::class)->setArgument('$rateLimiterClassMap', $rateLimiterClassMap);
    }

    /** @return array<string, Definition> */
    private function processResetPasswordEndpoints(ContainerBuilder $container): array
    {
        $endpoints = [
            'coop_tilleuls_forgot_password.controller.reset_password' => Type::PASSWORD_RESET_REQUEST,
            'coop_tilleuls_forgot_password.controller.get_token' => Type::PASSWORD_RESET_TOKEN_CHECK,
            'coop_tilleuls_forgot_password.controller.update_password' => Type::PASSWORD_RESET_CONFIRM,
        ];

        $rateLimiterClassMap = [];

        foreach ($endpoints as $id => $rateLimiterType) {
            $serviceKey = sprintf('limiter.%s', $rateLimiterType->value);
            $rateLimiterClassMap[$id] = $container->getDefinition($serviceKey);
        }

        return $rateLimiterClassMap;
    }
}
