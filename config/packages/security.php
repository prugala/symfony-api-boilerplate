<?php

declare(strict_types=1);

use App\Auth\Enum\Role;
use App\User\Entity\User;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('security', [
        'password_hashers' => [
            User::class => 'auto',
        ],
        'providers' => [
            'api_user_provider' => [
                'entity' => [
                    'class' => User::class,
                    'property' => 'email',
                ],
            ],
        ],
        'firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
            'auth' => [
                'pattern' => '^/api/auth',
                'stateless' => true,
                'json_login' => [
                    'check_path' => '/api/auth/token',
                    'success_handler' => 'lexik_jwt_authentication.handler.authentication_success',
                    'failure_handler' => 'lexik_jwt_authentication.handler.authentication_failure',
                ],
                'refresh_jwt' => [
                    'check_path' => '/api/auth/token/refresh',
                ],
            ],
            'api' => [
                'pattern' => '^/api',
                'stateless' => true,
                'jwt' => null,
            ],
        ],
        'access_control' => [
            [
                'path' => '^/api/(doc|auth/token|auth/register|auth/forgot-password)',
                'roles' => 'PUBLIC_ACCESS',
            ],
            [
                'path' => '^/api',
                'roles' => Role::USER->value,
            ]
        ],
    ]);
    if ($containerConfigurator->env() === 'test') {
        $containerConfigurator->extension('security', [
            'password_hashers' => [
                PasswordAuthenticatedUserInterface::class => [
                    'algorithm' => 'auto',
                    'cost' => 4,
                    'time_cost' => 3,
                    'memory_cost' => 10,
                ],
            ],
        ]);
    }
};
