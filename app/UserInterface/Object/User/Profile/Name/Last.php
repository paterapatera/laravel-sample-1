<?php

declare(strict_types=1);

namespace App\UserInterface\Object\User\Profile\Name;

/**
 * 姓
 */
class Last
{
    function __construct(public readonly string $value)
    {
    }
}
