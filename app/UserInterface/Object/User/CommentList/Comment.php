<?php

declare(strict_types=1);

namespace App\UserInterface\Object\User\CommentList;

class Comment
{
    function __construct(public readonly string $value)
    {
    }
}
