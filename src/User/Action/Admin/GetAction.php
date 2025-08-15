<?php

declare(strict_types=1);

namespace App\User\Action\Admin;

use App\Auth\Enum\Role;
use App\OpenApi\Attribute\SuccessResponse;
use App\Shared\Model\ApiResponse;
use App\User\Entity\User;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/users/{user}',
    name: 'users_get',
    methods: [Request::METHOD_GET]
)]
#[OA\Tag('User')]
#[IsGranted(Role::ADMIN->value)]
#[SuccessResponse(User::class, groups: ['user:get'])]
#[AsController]
final readonly class GetAction
{
    /** @return ApiResponse<User> */
    public function __invoke(User $user): ApiResponse
    {
        return new ApiResponse(
            data: $user,
            groups: ['user:get'],
        );
    }
}
