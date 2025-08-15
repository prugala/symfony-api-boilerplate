<?php

declare(strict_types=1);

namespace App\Auth\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class EmailAlreadyExists extends Constraint
{
    public string $message = 'email_already_exists';

    public function __construct(?array $groups = null, $payload = null)
    {
        parent::__construct([], $groups, $payload);
    }
}
