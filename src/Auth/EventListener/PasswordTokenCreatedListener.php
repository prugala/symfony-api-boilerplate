<?php

declare(strict_types=1);

namespace App\Auth\EventListener;

use CoopTilleuls\ForgotPasswordBundle\Event\CreateTokenEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: CreateTokenEvent::class)]
final readonly class PasswordTokenCreatedListener
{
    public function __invoke(CreateTokenEvent $event): void
    {
        // @TODO send email
        // $event->getPasswordToken()->getToken()
    }
}
