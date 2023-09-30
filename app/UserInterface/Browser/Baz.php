<?php

declare(strict_types=1);

namespace App\UserInterface\Browser;

use Illuminate\Support\Collection;

class Baz
{
}

///////////////////////////////////////////////

class User
{
    use Copyable;

    function __construct(
        public readonly Name $name,
        public readonly Tel $tel,
        public readonly CommentList $comments
    ) {
    }
}

class Name
{
    use Copyable;

    function __construct(public readonly string $value)
    {
    }
}

class Tel
{
    use Copyable;

    function __construct(public readonly string $value)
    {
    }
}

class CommentList
{
    use Copyable;

    /**
     * @param Comment[] $values
     */
    function __construct(public readonly array $values)
    {
    }

    /**
     * @param Comment[] $comments
     */
    function add(array $comments): self
    {
        return new self([...$this->values, ...$comments]);
    }

    /**
     * @param Comment[] $comments
     */
    static function addProc(array $comments): \Closure
    {
        return fn (CommentList $v) => $v->add($comments);
    }
}

class Comment
{
    use Copyable;

    function __construct(public readonly string $value)
    {
        if ($value === 'string2') throw new \Exception($value);
    }
}

$user = new User(
    name: new Name('tekun'),
    tel: new Tel('000-000-000'),
    comments: new CommentList([new Comment('1')]),
);
var_dump($user->comments);

$user2 = $user->merge([
    'name' => new Name('aska'),
    'tel' => fn (Tel $v) => new Tel($v->value . 'aaaaaa'),
    'comments' => CommentList::addProc([
        new Comment('333'),
        new Comment('335')
    ]),
]);
var_dump($user2);
