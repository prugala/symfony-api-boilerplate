<?php

declare(strict_types=1);

namespace App\Shared\EventListener;

use Pagerfanta\Exception\InvalidArgumentException;
use Pagerfanta\Exception\NotValidMaxPerPageException;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final readonly class PaginationExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$this->isSupported($exception)) {
            return;
        }

        $response = new JsonResponse();

        $code = Uuid::v5(Uuid::fromString(Uuid::NAMESPACE_DNS), $exception->getMessage())->toString();

        $field = match (true) {
            $exception instanceof OutOfRangeCurrentPageException => 'page',
            $exception instanceof NotValidMaxPerPageException => 'limit',
            default => 'unknown',
        };

        $data = [
            'violations' => [
                [
                    'field' => $field,
                    'message' => $exception->getMessage(),
                ],
            ],
            'code' => $code,
        ];

        /** @var non-empty-string $content */
        $content = json_encode($data);

        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        $response->setContent($content);

        $event->setResponse($response);
    }

    private function isSupported(\Throwable $exception): bool
    {
        if ($exception instanceof InvalidArgumentException) {
            return true;
        }

        return false;
    }
}
