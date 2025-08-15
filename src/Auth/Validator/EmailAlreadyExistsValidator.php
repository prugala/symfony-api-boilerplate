<?php

declare(strict_types=1);

namespace App\Auth\Validator;

use App\User\Repository\UserRepository;
use App\User\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class EmailAlreadyExistsValidator extends ConstraintValidator
{
    /** @param UserRepository $userRepository */
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof EmailAlreadyExists) {
            throw new UnexpectedTypeException($constraint, EmailAlreadyExists::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if ($this->userRepository->emailExists($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
