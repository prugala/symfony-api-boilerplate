<?php

declare(strict_types=1);

namespace App\User\Action\Admin;

use App\Auth\Enum\Role;
use App\OpenApi\Attribute\SuccessResponse;
use App\Shared\Model\ApiResponse;
use App\Shared\Model\PaginationRequest;
use App\User\Entity\User;
use App\User\Provider\UserProvider;
use OpenApi\Attributes as OA;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/users',
    name: 'users_list',
    methods: [Request::METHOD_GET]
)]
#[OA\Tag('User')]
#[IsGranted(Role::ADMIN->value)]
//#[SuccessResponse(User::class, groups: ['user:list'], isList: true)]
#[AsController]
final readonly class ListAction
{
    public function __construct(private UserProvider $provider)
    {
    }

    /** @return ApiResponse<Pagerfanta<User>> */
    public function __invoke(#[MapQueryString] PaginationRequest $paginationRequest): ApiResponse
    {
        return new ApiResponse(
            data: $this->provider->providePaginatedUsers($paginationRequest),
            groups: ['user:list'],
        );
    }
}
