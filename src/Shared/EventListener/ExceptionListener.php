<?php

declare(strict_types=1);

namespace App\Shared\EventListener;

use App\Shared\Exception\AbstractApiException;
use Pagerfanta\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\Exception\RateLimitExceededException;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
final readonly class ExceptionListener
{
    private const string DEFAULT_ERROR_MESSAGE = 'An error occurred';

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

        if ($exception instanceof AbstractApiException) {
            $response->setStatusCode($exception->statusCode);
            $message = $exception->getMessage();
        } else {
            $this->logger->error($exception->getMessage(), ['exception' => $exception, 'code' => $code]);

            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            $message = self::DEFAULT_ERROR_MESSAGE;
        }

        $data = [
            'message' => $message,
            'code' => $code,
        ];

        if ($_SERVER['APP_DEBUG']) {
            $data['message'] = $exception->getMessage();
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
            $data['trace'] = $exception->getTrace();
            $data['previous'] = $exception->getPrevious();
        }

        /** @var non-empty-string $content */
        $content = json_encode($data);

        $response->setContent($content);

        $event->setResponse($response);
    }

    private function isSupported(\Throwable $exception): bool
    {
        if (
            $exception instanceof ValidationFailedException
            || $exception instanceof UnprocessableEntityHttpException
            || $exception instanceof NotFoundHttpException
            || $exception instanceof MethodNotAllowedHttpException
            || $exception instanceof InvalidArgumentException
            || $exception instanceof RateLimitExceededException
            || $exception->getPrevious() instanceof ValidationFailedException
        ) {
            return false;
        }

        return true;
    }
}
