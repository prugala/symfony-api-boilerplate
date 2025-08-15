<?php

declare(strict_types=1);

namespace App\User\Provider;

use App\Shared\Model\PaginationRequest;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

final readonly class UserProvider
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    /** @return Pagerfanta<User> */
    public function providePaginatedUsers(PaginationRequest $paginationRequest): Pagerfanta
    {
        $queryBuilder = $this->userRepository->createQueryBuilder('user');
        $queryBuilder->setMaxResults($paginationRequest->limit);
        $queryBuilder->setFirstResult($paginationRequest->page * $paginationRequest->limit - $paginationRequest->limit);

        return Pagerfanta::createForCurrentPageWithMaxPerPage(new QueryAdapter($queryBuilder), $paginationRequest->page, $paginationRequest->limit);
    }
}
