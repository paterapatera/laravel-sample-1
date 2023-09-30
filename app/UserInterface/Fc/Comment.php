<?php

declare(strict_types=1);

namespace App\UserInterface\Fc;

/**
 * @phpstan-type CommentType string
 */
class Comment
{
    static function create(string $comment): string
    {
        self::validate($comment);
        return $comment;
    }

    /**
     * @phpstan-param CommentType $comment
     */
    static function validate(string $comment): void
    {
        if ($comment === 'string2') throw new \Exception($comment);
    }
}
