<?php

declare(strict_types=1);

namespace App\Shared\Model;

use Symfony\Component\HttpFoundation\Response;

/**  @template T */
final readonly class ApiResponse implements ApiResponseInterface
{
    /**
     * @param string[]              $groups
     * @param array<string, string> $headers
     * @param array<mixed, mixed>   $context
     */
    public function __construct(
        public mixed $data,
        public int $statusCode = Response::HTTP_OK,
        public array $groups = [],
        public array $headers = [],
        public array $context = [],
    ) {
    }
}
