<?php

namespace Tests\Unit;

use App\UserInterface\Fc\Record;
use PHPUnit\Framework\TestCase;

class USTest extends TestCase
{
    public function test_get(): void
    {
        $user = [
            'profile' => [
                'name' => 'aoyama yuta',
                'age' => '27',
                'birthday' => '1986-11-27',
            ],
            'contact' => [
                'tel' => '111-1111-1111',
                'address' => 'kanagawa hiragimachi',
            ],
            'comments' => [
                'こんにちは',
                'こんばんは',
                'おはよう',
            ],
        ];
        $profile = Record::get(['profile'])($user);
        $this->assertEquals([
            'name' => 'aoyama yuta',
            'age' => '27',
            'birthday' => '1986-11-27',
        ], $profile);

        $tel = Record::get(['contact', 'tel'])($user);
        $this->assertEquals('111-1111-1111', $tel);
    }

    public function test_map(): void
    {
        $fruits = [
            'apple' => 1,
            'orange' => 2,
        ];
        $result = Record::map(fn (int $v, string $k) => strval($v) . $k)($fruits);
        $this->assertEquals(['apple' => '1apple', 'orange' => '2orange'], $result);
    }

    public function test_filter(): void
    {
        $list = [
            'a1' => 1,
            'a2' => 2,
            'a3' => 3,
            'a4' => 2,
        ];
        $result = Record::filter(fn (int $v, string $k) => $v !== 2)($list);
        $this->assertEquals(['a1' => 1, 'a3' => 3], $result);
    }

    public function test_filter_fresh(): void
    {
        $list = [1, 2, 3, 2,];
        $result = Record::filterFresh(fn (int $v, string $k) => $v !== 2)($list);
        $this->assertEquals([1,  3], $result);
    }

    public function test_has(): void
    {
        $user = [
            'profile' => [
                'name' => 'aoyama yuta',
                'age' => '27',
                'birthday' => '1986-11-27',
            ],
        ];
        $hasName = Record::has(['profile', 'name'])($user);
        $this->assertEquals(true, $hasName);

        $hasHoge = Record::has(['profile', 'hoge'])($user);
        $this->assertEquals(false, $hasHoge);
    }

    public function test_pipe(): void
    {
        $compose = Record::pipe(
            fn ($x) => $x + 1,
            fn ($x) => $x * 2,
            fn ($x) => $x - 4,
        );
        $this->assertEquals(18, $compose(10));
    }

    public function test_headOrNull(): void
    {
        $this->assertEquals(1, Record::headOrNull([1, 2, 3]));
        $this->assertEquals(1, Record::headOrNull(['x' => 1, 'y' => 2, 'z' => 3]));
        $this->assertEquals(null, Record::headOrNull([]));
    }

    public function test_taile(): void
    {
        $this->assertEquals([2, 3], Record::tail([1, 2, 3]));
        $this->assertEquals(['y' => 2, 'z' => 3], Record::tail(['x' => 1, 'y' => 2, 'z' => 3]));
        $this->assertEquals([], Record::tail(['x' => 1]));
    }
}
