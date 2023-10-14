<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

use App\UserInterface\Object\User;
use App\UserInterface\Object\User\{Profile, CommentList};
use App\UserInterface\Object\User\Profile\Name;
use App\UserInterface\Object\User\Profile\Name\{First, Last};
use App\UserInterface\Object\User\CommentList\Comment;

class USTest2 extends TestCase
{
    public function test_lens(): void
    {
        $user = new User(
            new Profile(
                new Name(
                    new First('yuuki'),
                    new Last('aoyama'),
                )
            ),
            new CommentList([
                new Comment('comment1')
            ])
        );


        $d = $this->ss(1, Comment::class)([
            [new Comment('comment1'), fn (Comment $c) => new Comment($c->value)],
            [new Comment('comment1'), fn (Comment $c) => new Comment($c->value)],
            [new Comment('comment1'), fn (Comment $c) => new Comment($c->value)],
            [new Comment('comment1'), fn (Comment $c) => new Comment($c->value)],
            [new Comment('comment1'), fn (Comment $c) => new Comment($c->value)],
            [new Comment('comment1'), fn (Comment $c) => new Comment($c->value)],
        ]);
        \PHPStan\Dumptype($d);

        $this->assertEquals('yuuki', 'yuuki');
    }

    /**
     * @template T
     * @template U of scalar
     * @param U $i
     * @param class-string<T> $class
     * @return callable(array<array{T, callable(T):T}>):array<array{T, callable(T):T}>
     */
    function ss($i, $class)
    {
        $b = fn (array $c): bool => $c[1]($c[0]) !== new Comment('comment1' . strval($i));
        return fn ($list) => array_filter($list, $b);
    }
}
