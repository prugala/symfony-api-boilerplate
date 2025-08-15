<?php

declare(strict_types=1);

namespace App\Auth\EventListener;

use App\Auth\Model\ChangePasswordRequest;
use App\User\Entity\User;
use CoopTilleuls\ForgotPasswordBundle\Event\UpdatePasswordEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsEventListener(event: UpdatePasswordEvent::class)]
final readonly class ResetPasswordUpdateListener
{
    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function __invoke(UpdatePasswordEvent $event): void
    {
        /** @var string $password */
        $password = $event->getPassword();
        $changePasswordRequest = new ChangePasswordRequest($password);

        $constraintViolationList = $this->validator->validate($changePasswordRequest, groups: ['password:reset']);

        if ($constraintViolationList->count() > 0) {
            $violationFailedException = new ValidationFailedException('password', $constraintViolationList);

            throw new UnprocessableEntityHttpException(previous: $violationFailedException);
        }

        /** @var User $user */
        $user = $event->getPasswordToken()->getUser();
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->flush();
    }
}
