<?php

declare(strict_types=1);

namespace App\User\Repository;

interface UserRepositoryInterface
{
    public function emailExists(string $email): bool;
}
