<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->import('@CoopTilleulsForgotPasswordBundle/Resources/config/routing.xml')
        ->prefix('/api/auth/forgot-password');
    $routingConfigurator->add('coop_tilleuls_forgot_password.reset', '/api/auth/forgot-password')
        ->controller('coop_tilleuls_forgot_password.controller.reset_password')
        ->methods([
            'POST',
        ]);
};
