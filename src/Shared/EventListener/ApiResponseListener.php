<?php

declare(strict_types=1);

namespace App\Shared\EventListener;

use App\Shared\Model\ApiErrorResponse;
use App\Shared\Model\ApiResponse;
use App\Shared\Model\ApiResponseInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;

#[AsEventListener(event: KernelEvents::VIEW)]
final readonly class ApiResponseListener
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function __invoke(ViewEvent $event): void
    {
        $result = $event->getControllerResult();

        if (!$result instanceof ApiResponseInterface) {
            return;
        }

        assert($result instanceof ApiResponse || $result instanceof ApiErrorResponse);

        if ($result instanceof ApiErrorResponse) {
            $data = $this->getError($result);
        } else {
            $data = match (true) {
                $result->data instanceof Pagerfanta => $this->getList($result),
                default => $this->getItem($result),
            };
        }

        $response = new Response($data, $result->statusCode, $result->headers);
        $event->setResponse($response);
    }

    /** @param ApiResponse<mixed> $response */
    private function getItem(ApiResponseInterface $response): string
    {
        $groups = array_merge($response->groups, $response->context['groups'] ?? []);
        $context = $response->context;
        $context['groups'] = $groups;

        return $this->serializer->serialize(['data' => $response->data], 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));
    }

    /** @param ApiResponse<mixed> $response */
    private function getList(ApiResponseInterface $response): string
    {
        $groups = array_merge($response->groups, $response->context['groups'] ?? []);
        $context = $response->context;
        $context['groups'] = $groups;

        /** @var Pagerfanta<mixed> $data */
        $data = $response->data;

        return $this->serializer->serialize([
            'data' => $data->getCurrentPageResults(),
            'total' => $data->getNbResults(),
            'has_next_page' => $data->hasNextPage(),
            'has_previous_page' => $data->hasPreviousPage(),
        ], 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));
    }

    /** @param ApiErrorResponse $response */
    private function getError(ApiResponseInterface $response): string
    {
        $groups = array_merge($response->groups, $response->context['groups'] ?? []);
        $context = $response->context;
        $context['groups'] = $groups;

        $code = $response->code;

        if (null === $code) {
            $code = Uuid::v5(Uuid::fromString(Uuid::NAMESPACE_DNS), $response->message)->toString();
        }

        return $this->serializer->serialize(['message' => $response->message, 'code' => $code], 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));
    }
}
