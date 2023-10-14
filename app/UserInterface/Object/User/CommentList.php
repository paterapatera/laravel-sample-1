<?php

declare(strict_types=1);

namespace App\UserInterface\Object\User;

use App\UserInterface\Object\User\CommentList\Comment;

/**
 * コメントリスト
 */
class CommentList
{
    /**
     * @param Comment[] $list
     */
    function __construct(public readonly array $list)
    {
    }
}
