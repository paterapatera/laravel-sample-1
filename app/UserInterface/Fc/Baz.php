<?php

declare(strict_types=1);

namespace App\UserInterface\Fc;

use Illuminate\Support\Collection;

class Baz
{
}

///////////////////////////////////////////////

/**
 * @phpstan-import-type NameType from Name
 * @phpstan-import-type AgeType from Age
 * @phpstan-import-type TelType from Tel
 * @phpstan-import-type CommentListType from CommentList
 * @phpstan-type UserType array{
 *   name: NameType,
 *   age: AgeType,
 *   tel: TelType,
 *   comments: CommentListType,
 * }
 */
class User
{
    /**
     * @phpstan-param UserType $user
     */
    static function validate(array $user): void
    {
        Name::validate($user['name']);
        Age::validate($user['age']);
        Tel::validate($user['tel']);
        CommentList::validate($user['comments']);
    }

    /**
     * @template T of array
     * @phpstan-param UserType $user
     * @phpstan-param T $diff
     * @phpstan-return UserType
     */
    // static function update(array $user, array $diff): array
    // {
    //     $updatedUser = Record::merge($user, $diff);
    //     User::validate($updatedUser);
    //     return $updatedUser;
    // }
}

/**
 * @phpstan-import-type CommentType from Comment
 * @phpstan-type CommentListType CommentType[]
 */
class CommentList
{
    /**
     *  @phpstan-param CommentListType $newComments
     *  @return \Closure(CommentListType): CommentListType
     */
    static function add(array $newComments): \Closure
    {
        return fn (array $comments) => [...$comments, ...$newComments];
    }

    /**
     * @phpstan-param CommentListType $comments
     */
    static function validate(array $comments): void
    {
        foreach ($comments as $comment) {
            Comment::validate($comment);
        }
    }
}

/**
 * @phpstan-type NameType string
 */
class Name
{
    static function create(string $name): string
    {
        self::validate($name);
        return $name;
    }

    /**
     * @param NameType $name
     */
    static function validate(string $name): void
    {
        //
    }
}

/**
 * @phpstan-type TelType string
 */
class Tel
{
    /** @param string $tel */
    static function create(string $tel): string
    {
        self::validate($tel);
        return $tel;
    }

    /**
     * @phpsatn-param TelType $tel
     */
    static function validate(string $tel): void
    {
        if (mb_strlen($tel) > 13) throw new \Exception($tel);
    }
}

/**
 * @phpstan-type AgeType non-negative-int
 */
class Age
{
    /**
     * @phpstan-param AgeType $age
     * @return AgeType
     **/
    static function create(int $age): int
    {
        self::validate($age);
        return $age;
    }

    /**
     * @phpstan-param AgeType $age
     */
    static function validate(int $age): void
    {
        /** @phpstan-ignore-next-line */
        if ($age < 0) throw new \Exception(strval($age));
    }

    /**
     * @phpstan-param AgeType $age
     * @return AgeType
     **/
    static function increment(int $age): int
    {
        return $age + 1;
    }
}

// $user = [
//     'name' => Name::create('tekun'),
//     'age' => Age::create(10),
//     'tel' => Tel::create('000-000-000'),
//     'comments' => [Comment::create('1')],
// ];
// User::validate($user);
// 
// var_dump($user);
// 
// $updateUserData = Record::calc($user, [
//     'age' => Age::increment(...),
//     'tel' => Tel::create('aaaaaa'),
//     'comments' => CommentList::add([
//         Comment::create('333'),
//         Comment::create('335')
//     ]),
// ]);
// $user2 = User::update($user, $updateUserData);
// 
// var_dump($user2);
// 
// $a = (object)['aaaaa' => 1];
// $b = (object)['aaaaa' => 2];
// $s = array_diff([
//     'a' => ['b' => $a, 'c' => $a]
// ], [
//     'a' => ['b' => $b, 'c' => $b]
// ]);
// var_dump($s);
