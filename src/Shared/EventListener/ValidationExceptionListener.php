<?php

declare(strict_types=1);

namespace App\Shared\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final readonly class ValidationExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        /** @var UnprocessableEntityHttpException $exception */
        $exception = $event->getThrowable();

        if (!$this->isSupported($exception)) {
            return;
        }

        /** @var ValidationFailedException $validationFailedException */
        $validationFailedException = $exception->getPrevious();

        $response = new JsonResponse();

        $code = Uuid::v5(Uuid::fromString(Uuid::NAMESPACE_DNS), $exception->getMessage())->toString();
        $violations = [];

        foreach ($validationFailedException->getViolations() as $violation) {
            $violations[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        $data = [
            'code' => $code,
            'violations' => $violations,
        ];

        /** @var non-empty-string $content */
        $content = json_encode($data);

        $response->setContent($content);
        $response->setStatusCode($exception->getStatusCode());

        $event->setResponse($response);
    }

    private function isSupported(\Throwable $exception): bool
    {
        if (
            ($exception instanceof UnprocessableEntityHttpException
            || $exception instanceof NotFoundHttpException)
            && $exception->getPrevious() instanceof ValidationFailedException
        ) {
            return true;
        }

        return false;
    }
}
