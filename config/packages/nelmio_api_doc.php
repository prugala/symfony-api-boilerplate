<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $documentation = [
        'info' => [
            'title' => '%env(APP_NAME)%',
            'description' => '%env(APP_DESCRIPTION)%',
            'version' => '%env(APP_VERSION)%',
        ],
        'tags' => [
            [
                'name' => 'Authentication',
                'description' => 'Authentication endpoints',
            ],
            [
                'name' => 'Forgot password',
                'description' => 'Forgot password endpoints',
            ],
        ],
    ];

    $documentation['paths'] = [];

    // Authentication
    $documentation['paths'] = array_merge($documentation['paths'], [
        '/api/auth/token' => [
            'post' => [
                'tags' => ['Authentication'],
                'summary' => 'Get JWT token',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'username' => [
                                        'type' => 'string',
                                    ],
                                    'password' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'JWT token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => [
                                            'type' => 'string',
                                        ],
                                        'refresh_token' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'Invalid credentials',
                    ],
                    '400' => [
                        'description' => 'Invalid request',
                    ],
                ],
            ]
        ],
        '/api/auth/token/refresh' => [
            'post' => [
                'tags' => ['Authentication'],
                'summary' => 'Refresh JWT token',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'refresh_token' => [
                                        'type' => 'string',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'JWT token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => [
                                            'type' => 'string',
                                        ],
                                        'refresh_token' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => [
                        'description' => 'Invalid credentials',
                    ],
                ],
            ],
        ],
    ]);

    // Reset password
    $documentation['paths'] = array_merge($documentation['paths'], [
        '/api/auth/forgot-password' => [
            'post' => [
                'tags' => ['Forgot password'],
                'operationId' => 'postForgotPassword',
                'summary' => 'Generates a token and send email',
                'responses' => [
                    204 => [
                        'description' => 'Valid email address, no matter if user exists or not',
                    ],
                    422 => [
                        'description' => 'Missing email parameter or invalid format',
                    ],
                ],
                'requestBody' => [
                    'description' => 'Request a new password',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/ForgotPassword:request',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/api/auth/reset-password/{tokenValue}' => [
            'get' => [
                'tags' => ['Forgot password'],
                'operationId' => 'getForgotPassword',
                'summary' => 'Validates token',
                'responses' => [
                    200 => [
                        'description' => 'Authenticated user',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ForgotPassword:validate',
                                ],
                            ],
                        ],
                    ],
                    404 => [
                        'description' => 'Token not found or expired',
                    ],
                ],
                'parameters' => [
                    [
                        'name' => 'tokenValue',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'post' => [
                'tags' => ['Forgot password'],
                'operationId' => 'postForgotPasswordToken',
                'summary' => 'Resets user password from token',
                'responses' => [
                    204 => [
                        'description' => 'Password changed',
                    ],
                    422 => [
                        'description' => 'Missing password parameter or invalid format',
                    ],
                    404 => [
                        'description' => 'Token not found',
                    ],
                ],
                'parameters' => [
                    [
                        'name' => 'tokenValue',
                        'in' => 'path',
                        'required' => true,
                        'schema' => [
                            'type' => 'string',
                        ],
                    ],
                ],
                'requestBody' => [
                    'description' => 'Reset password',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/ForgotPassword:reset',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $documentation['components']['schemas']['ForgotPassword:request'] = array_merge([
        'description' => 'New password request object',
    ], [
        'type' => 'object',
        'required' => ['email'],
        'properties' => [
            'email' => [
                'type' => ['string'],
            ],
        ],
    ]);

    $documentation['components']['schemas']['ForgotPassword:validate'] = [
        'type' => 'object',
        'allOf' => [
            ['$ref' => '#/components/schemas/User'],
        ],
        'properties' => [
            'expiresAt' => [
                'type' => ['string'],
                'format' => 'date-time',
                'example' => '2025-01-01T00:00:00+00:00'
            ],
        ],
        'groups' => ['user:get'],
        'description' => 'Authenticated user',
    ];

    $documentation['components']['schemas']['ForgotPassword:reset'] = array_merge([
        'description' => 'Reset password object',
    ], [
        'type' => 'object',
        'required' => ['password'],
        'properties' => [
            'password' => ['type' => 'string'],
        ],
    ]);

    $containerConfigurator->extension('nelmio_api_doc', [
        'use_validation_groups' => true,
        'documentation' => $documentation,
        'areas' => [
            'security' => [
                'Bearer' => [
                    'type' => 'http',
                    'in' => 'header',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                ],
            ],
            'path_patterns' => [
                '^/api(?!/doc$)',
            ],
        ],
    ]);
};
