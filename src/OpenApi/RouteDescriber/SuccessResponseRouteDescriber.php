<?php

declare(strict_types=1);

namespace App\OpenApi\RouteDescriber;

use App\OpenApi\Attribute\SuccessResponse;
use App\Shared\Model\ApiResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;
use OpenApi\Annotations\OpenApi;
use OpenApi\Attributes as OA;
use Pagerfanta\Pagerfanta;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Route;

#[AutoconfigureTag(name: 'nelmio_api_doc.route_describer')]
final readonly class SuccessResponseRouteDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function describe(OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        $attributes = $reflectionMethod->getDeclaringClass()->getAttributes(SuccessResponse::class);

        if (!$attributes) {
            return;
        }

        $responses = [];

        foreach ($attributes as $attribute) {
            /** @var SuccessResponse $attributeInstance */
            $attributeInstance = $attribute->newInstance();

            $responses[] = $this->createResponse($attributeInstance);
        }

        foreach ($this->getOperations($api, $route) as $operation) {
            $operation->merge($responses);
        }
    }

    private function createResponse(SuccessResponse $attribute): OA\Response
    {
        $model = new Model(type: $attribute->modelClass, groups: $attribute->groups);

        if ($attribute->isList) {
            $properties = [
                new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: $model)
                ),
                new OA\Property(
                    property: 'page',
                    type: 'integer'
                ),
                new OA\Property(
                    property: 'limit',
                    type: 'integer'
                ),
                new OA\Property(
                    property: 'has_next_page',
                    type: 'boolean'
                ),
                new OA\Property(
                    property: 'has_previous_page',
                    type: 'boolean'
                ),
            ];
        } else {
            $properties = [
                new OA\Property(
                    property: 'data',
                    ref: $model
                ),
            ];
        }

        $schema = new OA\Schema(
            schema: $attribute->isList ? Pagerfanta::class : ApiResponse::class,
            properties: $properties
        );

        return new OA\Response(
            response: $attribute->statusCode,
            description: $attribute->description,
            content: [
                'application/json' => new OA\MediaType(
                    mediaType: 'application/json',
                    schema: $schema
                ),
            ],
        );
    }
}
