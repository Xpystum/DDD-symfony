<?php

declare(strict_types=1);

namespace App\User\Application\Exception;

use Exception;

class UserNotFoundException extends Exception
{
    public static function byId(string $id): self
    {
        return new self("Пользователь [$id] не найден.");
    }

    public static function byEmail(string $email): self
    {
        return new self("Пользователь [$email] не найден.");
    }
}
