<?php

declare(strict_types=1);

namespace App\Auth\Model;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

final readonly class ChangePasswordRequest
{
    public function __construct(
        #[\SensitiveParameter]
        #[PasswordStrength(minScore: PasswordStrength::STRENGTH_WEAK, groups: ['password:change', 'password:reset'], message: 'too_weak')]
        #[Length(min: 4, groups: ['password:change', 'password:reset'])]
        #[NotBlank(groups: ['password:change', 'password:reset'])]
        public string $password,
        #[NotBlank(groups: ['password:change'])]
        public ?string $oldPassword = null,
    ) {
    }
}
