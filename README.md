# Symfony REST API Boilerplate

> A Docker-based boilerplate for building REST APIs with Symfony.  
> Docker configuration based on [Symfony Docker](https://github.com/dunglas/symfony-docker)

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Features

* OpenAPI (Swagger https://localhost/api/doc / https://localhost/api/doc.json)
* JWT Authentication with Refresh Token
* Reset password with email
* Pagination with Pagerfanta
* API Responses with `ApiResponse` and `ApiErrorResponse`
* Serialization groups based on roles
* Exception handling
* Simple documentation of success responses
* Unit tests with PHPUnit
* PHPStan
* PHP CS Fixer

### API Responses with `ApiResponse`

- `ApiResponse` - for successful responses: single object, paginated collection (`Pagerfanta` object)
- `ApiErrorResponse` - for error responses

Example:
`return new ApiResponse($post, groups: ['post:get']);`

```
{
    "data": {
        "id": 1,
        "title": "My first post",
        "description": "This is my first post",
    }
}
```

`return new ApiResponse($posts, groups: ['post:list']);`

```
{
    "data": [
        {
            "id": 1,
            "title": "My first post",
            "description": "This is my first post",
        },
    ],
    "total": 2,
    "has_next_page": true,
    "has_previous_page": false
}        
```

### Serialization groups based on roles
Convention in group names is `entity:action`. For example, `user:read` means that the entity is `User` and the action is `read`.
So if you want to serialize some field in `User` entity only for `ROLE_USER` role, you should add `user:read:user` in serialization configuration. 
In serialization groups you don't need to specify role name, it will be added automatically.

Example:
Serialization configuration:
```xml
<attribute name="description">
    <group>post:get:admin</group>
</attribute>
```

Controller action:
```php
public function __invoke()
{
    return new ApiResponse($post, groups: ['post:get']);
}
```

When you call this action as `ROLE_ADMIN`, you will get `description` field in response.

### Simple documentation of success responses
To document your API responses use attribute `SuccessResponse`. In OpenAPI documentation you will see the response related to ApiResponse object.

Example:
- for item: `#[SuccessResponse(ExampleResponse::class, groups: ['example:get'])]`
- for collection: `#[SuccessResponse(ExampleResponse::class, groups: ['example:list'], isList: true)]`


## TODO
 - fix trailing slash in route `/api/auth/forgot-password/`
 - add Notifier and email for reset password
 - fix problem with api doc when using `SuccessResponse` in multiple controllers
 - add change password by user
