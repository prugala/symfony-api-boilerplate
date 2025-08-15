<?php

declare(strict_types=1);

namespace App\Tests\Auth\Validator;

use App\Auth\Validator\EmailAlreadyExists;
use App\Auth\Validator\EmailAlreadyExistsValidator;
use App\User\Repository\UserRepositoryInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class EmailAlreadyExistsValidatorTest extends ConstraintValidatorTestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<UserRepositoryInterface> */
    private ObjectProphecy $repository;

    public function testItDoesNothingIfEmailIsUnique(): void
    {
        $this->repository->emailExists('unique@gmail.com')->willReturn(false);
        $this->validator->validate('unique@gmail.com', new EmailAlreadyExists());

        $this->assertNoViolation();
    }

    public function testItAddsViolationIfEmailAlreadyExists(): void
    {
        $constraint = new EmailAlreadyExists();

        $this->repository->emailExists('unique@gmail.com')->willReturn(true);
        $this->validator->validate('unique@gmail.com', $constraint);

        $this->buildViolation($constraint->message)
            ->assertRaised();
    }

    protected function createValidator(): ConstraintValidatorInterface
    {
        $this->repository = $this->prophesize(UserRepositoryInterface::class);

        return new EmailAlreadyExistsValidator($this->repository->reveal());
    }
}
