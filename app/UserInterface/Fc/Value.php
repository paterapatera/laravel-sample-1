<?php

declare(strict_types=1);

namespace App\UserInterface\Fc;

class Value
{
    /**
     * @template VAL
     * @template ARG
     * @param VAL|(\Closure(ARG): VAL) $value
     * @param ARG $arg
     * @return VAL
     */
    static function getOrRun($value, $arg)
    {
        if ($value instanceof \Closure) {
            return $value($arg);
        } else {
            return $value;
        }
    }
}
