<?php

namespace App\Application;

class UserUpdateService
{
  function update()
  {
    try {
      $a = ARepo::find(1);
      $updatedA = $a->update(3);
      ARepo::save($updatedA);
    } catch (\Exception $e) {
      print_r($e->getMessage());
    } catch (\Throwable $e) {
      print_r('th');
    }
  }

  function info()
  {
    // ユーザを取得する
    // ユーザを返す
  }
}

class A
{
  function __construct(public int $a)
  {
  }

  function update(int $i): static
  {
    return new A($i);
  }
}

class ARepo
{
  static function find(int $i): A
  {
    return new A($i);
  }

  static function save(A $a): void
  {
    print_r("save now! $a->a");
  }
}

class Pipe
{
  static function pipe(array $fns)
  {
    collect($fns)->reduce(fn ($v, $fn) => $fn($v));
  }
}
