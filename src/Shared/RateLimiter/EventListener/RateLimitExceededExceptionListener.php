<?php

declare(strict_types=1);

namespace App\Shared\RateLimiter\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\Exception\RateLimitExceededException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final readonly class RateLimitExceededExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$this->isSupported($exception)) {
            return;
        }

        $response = new Response();
        $response->setStatusCode(Response::HTTP_TOO_MANY_REQUESTS);

        $event->setResponse($response);
    }

    private function isSupported(\Throwable $exception): bool
    {
        if ($exception instanceof RateLimitExceededException) {
            return true;
        }

        return false;
    }
}
