<?php

declare(strict_types=1);

namespace App\UserInterface\Object\User;

use App\UserInterface\Object\User\Profile\Name;

/**
 * プロフィール
 */
class Profile
{
    function __construct(
        public readonly Name $name,
    ) {
    }
}
