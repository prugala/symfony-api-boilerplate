<?php

declare(strict_types=1);

namespace App\OpenApi\Attribute;

use Symfony\Component\HttpFoundation\Response;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class SuccessResponse
{
    /** @param string[] $groups */
    public function __construct(
        public string $modelClass,
        public int $statusCode = Response::HTTP_OK,
        public string $description = '',
        public array $groups = [],
        public bool $isList = false,
    ) {
    }
}
