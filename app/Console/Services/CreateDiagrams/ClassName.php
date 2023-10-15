<?php

namespace App\Console\Services\CreateDiagrams;

class ClassName
{
    /** @phpstan-assert-if-true class-string $className */
    static function isClass(string $className): bool
    {
        return class_exists($className);
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return \ReflectionClass<T>
     */
    static function toReflectionClass(string $className): \ReflectionClass
    {
        return new \ReflectionClass($className);
    }
}
