<?php

declare(strict_types=1);

namespace App\Shared\Model;

use Symfony\Component\HttpFoundation\Response;

final readonly class ApiErrorResponse implements ApiResponseInterface
{
    /**
     * @param string[]              $groups
     * @param array<string, string> $headers
     * @param array<mixed, mixed>   $context
     */
    public function __construct(
        public string $message,
        public ?string $code = null,
        public int $statusCode = Response::HTTP_BAD_REQUEST,
        public array $groups = [],
        public array $headers = [],
        public array $context = [],
    ) {
    }
}
