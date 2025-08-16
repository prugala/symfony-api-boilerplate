<?php

declare(strict_types=1);

namespace App\Shared\RateLimiter\Enum;

enum Type: string
{
    case REGISTER = 'register';
    case LOGIN = 'login';
    case PASSWORD_RESET_REQUEST = 'password_reset_request';
    case PASSWORD_RESET_CONFIRM = 'password_reset_confirm';
    case PASSWORD_RESET_TOKEN_CHECK = 'password_reset_token_check';
}
