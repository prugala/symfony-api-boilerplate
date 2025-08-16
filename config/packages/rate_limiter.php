<?php

declare(strict_types=1);

use App\Shared\RateLimiter\Enum\Type;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()->set('rate_limiter.enabled', true);

    $containerConfigurator->extension('framework', [
        'rate_limiter' => [
            Type::REGISTER->value => [
                'policy' => 'fixed_window',
                'limit' => 5,
                'interval' => '60 minutes',
            ],
            Type::LOGIN->value => [
                'policy' => 'fixed_window',
                'limit' => 3,
                'interval' => '10 minutes',
            ],
            Type::PASSWORD_RESET_REQUEST->value => [
                'policy' => 'fixed_window',
                'limit' => 3,
                'interval' => '60 minutes',
            ],
            Type::PASSWORD_RESET_CONFIRM->value => [
                'policy' => 'fixed_window',
                'limit' => 3,
                'interval' => '60 minutes',
            ],
            Type::PASSWORD_RESET_TOKEN_CHECK->value => [
                'policy' => 'fixed_window',
                'limit' => 3,
                'interval' => '60 minutes',
            ],
        ],
    ]);
};
