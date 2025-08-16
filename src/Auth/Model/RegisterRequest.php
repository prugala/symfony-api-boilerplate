<?php

declare(strict_types=1);

namespace App\Auth\Model;

use App\Auth\Validator\EmailAlreadyExists;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PasswordStrength;

final readonly class RegisterRequest
{
    public function __construct(
        #[Email(mode: Email::VALIDATION_MODE_STRICT)]
        #[EmailAlreadyExists]
        #[NotBlank]
        public string $email,
        #[\SensitiveParameter]
        #[PasswordStrength(minScore: PasswordStrength::STRENGTH_WEAK, message: 'too_weak')]
        #[Length(min: 4)]
        #[NotBlank]
        public string $password,
    ) {
    }
}
