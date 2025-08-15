<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('index', '/');
    $routingConfigurator->import('../src/', 'attribute')
        ->prefix('/api')
        ->namePrefix('api_')
        ->defaults([
            '_format' => 'json',
        ])
    ;
};
