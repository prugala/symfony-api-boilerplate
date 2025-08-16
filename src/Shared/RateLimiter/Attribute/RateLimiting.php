<?php

declare(strict_types=1);

namespace App\Shared\RateLimiter\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class RateLimiting
{
    public function __construct(
        public string $configuration,
    ) {
    }
}
