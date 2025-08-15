<?php

declare(strict_types=1);

use App\Auth\Entity\PasswordToken;
use App\User\Entity\User;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('coop_tilleuls_forgot_password', [
        'password_token' => [
            'class' => PasswordToken::class,
            'expires_in' => '1 day',
            'user_field' => 'user',
            'serialization_groups' => [
                'password-token:get',
            ],
        ],
        'user' => [
            'class' => User::class,
            'email_field' => 'email',
            'password_field' => 'password',
            'authorized_fields' => [
                'email',
            ],
        ],
    ]);
};
