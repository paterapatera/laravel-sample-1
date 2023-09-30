<?php

declare(strict_types=1);

namespace App\UserInterface\Browser;

trait Valuable
{
    function __get($name)
    {
        $reflection = new \ReflectionMethod($this, $name);
        $argc = $reflection->getNumberOfParameters();
        if ($argc === 0) {
            return $this->$name();
        } else {
            throw new \Error("[$name]というメンバー変数や引数なし関数は存在しません");
        }
    }
}
