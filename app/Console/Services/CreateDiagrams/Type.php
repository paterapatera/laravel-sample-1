<?php

namespace App\Console\Services\CreateDiagrams;

class Type
{
    /** @phpstan-assert-if-true \ReflectionNamedType $type */
    static function isReflectionNamedType(?\ReflectionType $type): bool
    {
        return $type instanceof \ReflectionNamedType;
    }

    /** @phpstan-assert-if-true \ReflectionNamedType $type */
    static function isClass(?\ReflectionType $type): bool
    {
        if (!$type instanceof \ReflectionNamedType) return false;
        return ClassName::isClass($type->getName());
    }
}
