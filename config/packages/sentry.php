<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\ErrorHandler\Error\FatalError;

return static function (ContainerConfigurator $containerConfigurator): void {
    if ($containerConfigurator->env() === 'prod') {
        $containerConfigurator->extension('sentry', [
            'dsn' => '%env(SENTRY_DSN)%',
            'register_error_listener' => false,
        ]);

        $containerConfigurator->extension('monolog', [
            'handlers' => [
                'sentry' => [
                    'type' => 'sentry',
                    'hub_id' => Sentry\State\HubInterface::class,
                    'level' => \Monolog\Level::Error->value,
                    'fill_extra_context' => true,
                ],
            ],
        ]);
    }
};
