<?php

declare(strict_types=1);

namespace App\UserInterface\Object;

use App\UserInterface\Object\User\CommentList;
use App\UserInterface\Object\User\Profile;

/**
 * ユーザー
 */
class User
{
    function __construct(
        public readonly Profile $profile,
        public readonly CommentList $commentList
    ) {
    }
}
