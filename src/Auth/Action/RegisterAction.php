<?php

declare(strict_types=1);

namespace App\Auth\Action;

use App\Auth\Model\RegisterRequest;
use App\Auth\RegisterUserAction;
use App\Shared\RateLimiter\Attribute\RateLimiting;
use App\Shared\RateLimiter\Enum\Type;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[RateLimiting(Type::REGISTER->value)]
#[Route(
    path: '/auth/register',
    name: 'auth_register',
    methods: [Request::METHOD_POST]
)]
#[OA\Tag('Authentication')]
#[AsController]
final readonly class RegisterAction
{
    public function __construct(private RegisterUserAction $registerUserAction)
    {
    }

    public function __invoke(#[MapRequestPayload] RegisterRequest $registerRequest): Response
    {
        return $this->registerUserAction->__invoke($registerRequest);
    }
}
