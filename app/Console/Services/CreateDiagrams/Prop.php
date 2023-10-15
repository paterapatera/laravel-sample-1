<?php

namespace App\Console\Services\CreateDiagrams;

class Prop
{
    static function isClass(\ReflectionProperty $prop): bool
    {
        return Type::isClass($prop->getType());
    }

    /** @return \ReflectionClass<object> */
    static function toReflectionClass(\ReflectionProperty $prop): \ReflectionClass
    {
        return new \ReflectionClass(self::getClassName($prop));
    }

    /** @return class-string */
    static function getClassName(\ReflectionProperty $prop): string
    {
        $type = $prop->getType();
        if (!Type::isReflectionNamedType($type)) throw new \Exception('このプロパティはクラスではありません: ' . $type);
        $className = $type->getName();
        if (!ClassName::isClass($className)) throw new \Exception('このプロパティはクラスではありません: ' . $className);
        return $className;
    }
}
