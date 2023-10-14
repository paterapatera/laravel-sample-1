<?php

declare(strict_types=1);

namespace App\UserInterface\Object;

#[\Attribute]
class Modeling
{
    function __construct(public readonly string $className)
    {
        if (!class_exists($className)) throw new \Exception('存在しないクラスです: ' . $className);
    }
}
