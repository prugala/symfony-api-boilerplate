<?php

declare(strict_types=1);

namespace App\Shared\Model;

use Symfony\Component\Validator\Constraints\Range;

final class PaginationRequest
{
    #[Range(min: 1)]
    public int $page = 1;
    #[Range(min: 1, max: 100)]
    public int $limit = 10;
}
