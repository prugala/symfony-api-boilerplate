<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routingConfigurator): void {
    $routingConfigurator->add('app.swagger_ui', '/api/doc')
        ->controller('nelmio_api_doc.controller.swagger_ui')
        ->methods([
        'GET',
    ]);
    $routingConfigurator->add('app.swagger', '/api/doc.json')
        ->controller('nelmio_api_doc.controller.swagger')
        ->methods([
        'GET',
    ]);
};
