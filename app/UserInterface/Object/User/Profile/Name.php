<?php

declare(strict_types=1);

namespace App\UserInterface\Object\User\Profile;

use App\UserInterface\Object\User\Profile\Name\{First, Last};

/**
 * 名前
 */
class Name
{
    function __construct(
        public readonly First $first,
        public readonly Last $last,
    ) {
    }
}
