<?php

declare(strict_types=1);

namespace App\Shared\Exception;

use Symfony\Component\HttpFoundation\Response;

abstract class AbstractApiException extends \Exception
{
    public int $statusCode;

    final public function __construct()
    {
        parent::__construct();
    }

    public static function create(string $message, int $statusCode = Response::HTTP_BAD_REQUEST): self
    {
        $exception = new static();
        $exception->message = $message;
        $exception->statusCode = $statusCode;

        return $exception;
    }
}
