<?php

declare(strict_types=1);

namespace App\Shared\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final readonly class NotFoundExceptionListener
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$this->isSupported($exception)) {
            return;
        }

        $response = new JsonResponse();

        $code = Uuid::v5(Uuid::fromString(Uuid::NAMESPACE_DNS), $exception->getMessage())->toString();

        $this->logger->error($exception->getMessage(), ['exception' => $exception, 'code' => $code]);

        $response->setStatusCode($exception instanceof NotFoundHttpException ? Response::HTTP_NOT_FOUND : Response::HTTP_METHOD_NOT_ALLOWED);

        $data = [
            'message' => $exception->getMessage(),
            'code' => $code,
        ];

        /** @var non-empty-string $content */
        $content = json_encode($data);

        $response->setContent($content);

        $event->setResponse($response);
    }

    private function isSupported(\Throwable $exception): bool
    {
        if (
            ($exception instanceof NotFoundHttpException
            || $exception instanceof MethodNotAllowedHttpException)
            && !$exception->getPrevious() instanceof ValidationFailedException
        ) {
            return true;
        }

        return false;
    }
}
