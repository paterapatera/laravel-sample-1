<?php

declare(strict_types=1);

namespace App\UserInterface\Object;

class Record
{
    const NO_DIFF = '_NO-DIFF';
    /**
     * @param list<array-key> $lensKeys
     * @return array{get: callable, set: callable}
     */
    static function lensByPath(array $lensKeys): array
    {
        $head = self::head($lensKeys);
        $tail = self::tail($lensKeys);
        $mapLens = self::map(fn ($key) => self::lens($key));
        return array_reduce(
            $mapLens($tail),
            self::lensCompose(...),
            self::lens($head)
        );
    }

    /**
     * @param array{get: callable, set: callable} $lens1
     * @param array{get: callable, set: callable} $lens2
     * @return array{get: callable, set: callable}
     */
    static function lensCompose(array $lens1, array $lens2): array
    {
        return [
            'get' => fn (array $record) => $lens2['get']($lens1['get']($record)),
            'set' => fn (array $record, $value) => $lens1['set']($record, $lens2['set']($lens1['get']($record), $value))
        ];
    }

    /**
     * @param array-key $key
     * @return array{get: callable, set: callable}
     */
    static function lens($key): array
    {
        return [
            'get' => self::get($key),
            'set' => self::set($key),
        ];
    }

    /**
     * @param array-key $key
     */
    static function set(string|int $key): callable
    {
        return function (array $record, mixed $value) use ($key) {
            return [...$record, $key => $value];
        };
    }

    /**
     * @param array-key $key
     */
    static function get(string|int $key): callable
    {
        return function (array $record) use ($key) {
            if (!array_key_exists($key, $record)) {
                $message = '存在しないキーを取得しようとしています|paths: ';
                throw new \Exception($message . json_encode($key));
            }
            return $record[$key];
        };
    }

    /**
     * @param array-key $key
     */
    static function getOr(string|int $key, mixed $deffault = null): callable
    {
        return function (array $record) use ($key, $deffault) {
            try {
                return self::get($key)($record);
            } catch (\Exception $_) {
                return $deffault;
            }
        };
    }

    /**
     * @template V
     * @template R
     * @param callable(V, array-key): R $f
     * @return callable(V[]): R[]
     */
    static function map(callable $f): callable
    {
        return function (array $record) use ($f) {
            $result = [];
            foreach ($record as $k => $v) {
                $result[$k] = $f($v, $k);
            }
            return $result;
        };
    }

    /**
     * @template V
     * @param callable(V, array-key): boolean $f
     * @return callable(V[]): V[]
     */
    static function filter(callable $f): callable
    {
        $mode = ARRAY_FILTER_USE_BOTH;
        return fn (array $record) => array_filter($record, $f, $mode);
    }

    /**
     * @template V
     * @param callable(V, array-key): boolean $f
     * @return callable(V[]): V[]
     */
    static function filterFresh(callable $f): callable
    {
        return self::pipe(
            self::filter($f),
            array_values(...)
        );
    }

    static function values(): callable
    {
        return fn (array $record) => array_values($record);
    }

    /**
     * @param list<array-key> $paths
     * @return callable(mixed[]): boolean
     */
    static function has(array $paths): callable
    {
        return function (array $record) use ($paths) {
            try {
                self::lensByPath($paths)['get']($record);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        };
    }

    /**
     * @template V
     * @param array<V> $list
     * @return V 
     */
    static function head(array $list): mixed
    {
        if (count($list) === 0) {
            $message = '配列の先頭を取得できませんでした|list: ';
            throw new \Exception($message . json_encode($list));
        }

        return array_values($list)[0];
    }

    /**
     * @template V
     * @param array<V> $list
     * @return ?V 
     */
    static function headOrNull(array $list): mixed
    {
        try {
            return self::head($list);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @template V
     * @param array<V> $list
     * @return array<V>
     */
    static function tail(array $list): array
    {
        return array_slice($list, 1);
    }

    static function pipe(callable ...$funcs): callable
    {
        $compose = function ($result, $func) {
            return $func($result);
        };
        return fn ($idetity) => array_reduce($funcs, $compose, $idetity);
    }

    /**
     * @template V of array
     * @param V $data1
     * @param V $data2
     * @return array<mixed>
     */
    static function diffObjects(array $data1, array $data2): array
    {
        if ($data1 === $data2) {
            return [];
        }

        $keys = array_unique(array_merge(array_keys($data1), array_keys($data2)));

        $diffPrc = function ($current, $key) use ($data1, $data2) {
            $getByKey = self::getOr($key);
            $res = self::diff($getByKey($data2))($getByKey($data1));
            if ((!is_array($res) || !empty($res)) &&
                ($res !== self::NO_DIFF)
            ) {
                $current[$key] = $res;
            }
            return $current;
        };
        return array_reduce($keys, $diffPrc, []);
    }

    /**
     * @template V
     * @param V $data2
     * @return callable(mixed): mixed
     */
    static function diff($data2): callable
    {
        return function ($data1) use ($data2) {
            if (is_array($data1) && is_array($data2)) {
                return self::diffObjects($data1, $data2);
            }

            if ($data1 !== $data2) {
                return $data2;
            }

            return self::NO_DIFF;
        };
    }

    /**
     * @template V
     * @param V $diffData
     * @param array<array-key> $path
     * @return (array{path: array<array-key>, value: V}|array{path: array<array-key>, value: V}[])
     */
    static function updatePaths($diffData, $path = [])
    {
        if (is_array($diffData)) {
            $paths = [];
            foreach ($diffData as $key => $value) {
                $result = self::updatePaths($value, [...$path, $key]);
                if (array_key_exists('path', $result)) {
                    $paths[] = $result;
                } else {
                    $paths = [...$paths, ...$result];
                }
            }
            return $paths;
        } else {
            return ['path' => $path, 'value' => $diffData];
        }
    }

    /**
     * @template V of array
     * @template D of array
     * @param V $diffData
     * @phpstan-param D $data
     * @return array<mixed>
     */
    static function update(array $diffData, array $data): array
    {
        $updatePaths = self::updatePaths($diffData);
        return array_reduce(
            $updatePaths,
            fn ($current, $path) => self::lensByPath($path['path'])['set']($current, $path['value']),
            $data,
        );
    }

    // /**
    //  * @template SRC of array
    //  * @template DIFF of array
    //  * @phpstan-param SRC $src
    //  * @phpstan-param DIFF $runMethods
    //  * @phpstan-return DIFF
    //  */
    // static function calc(array $src, array $runMethods): array
    // {
    //     return self::map($runMethods, fn ($v, string|int $k) => Value::getOrRun($v, $src[$k]));
    // }

    // /**
    //  * @template SRC of array
    //  * @template DIFF of array
    //  * @param SRC $src
    //  * @param DIFF $diff
    //  * @return SRC
    //  */
    // static function merge(array $src, array $diff): array
    // {
    //     return array_replace_recursive($src, $diff);
    // }
}
