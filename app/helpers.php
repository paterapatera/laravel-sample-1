<?php

if (!function_exists('valmap')) {
    /**
     * @template T
     * @template U
     * @param T $value
     * @param callable (T):U $callback
     * @param U|null|callable ():U $default
     * @return U|null 
     */
    function valmap(mixed $value, callable $callback, mixed $default = null): mixed
    {
        if (!is_null($value)) {
            return $callback($value);
        }

        if (is_callable($default)) {
            return $default();
        }

        return $default;
    }
}

if (!function_exists('valmapOrFail')) {
    /**
     * @template T
     * @template U
     * @param T $value
     * @param callable (T):U $callback
     * @param Throwable $error
     * @return U
     */
    function valmapOrFail(mixed $value, callable $callback, Throwable $error): mixed
    {
        if (is_null($value)) {
            throw $error;
        }

        return $callback($value);
    }
}
