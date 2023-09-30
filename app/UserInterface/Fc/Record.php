<?php

declare(strict_types=1);

namespace App\UserInterface\Fc;

class Record
{
    /** @param list<string> $paths */
    static function get(array $paths): callable
    {
        return function (array $record) use ($paths) {
            $current = $record;
            foreach ($paths as $path) {
                if (!array_key_exists($path, $current)) {
                    $message = '存在しないキーを取得しようとしています|paths: ';
                    throw new \Exception($message . json_encode($paths));
                }
                $current = $current[$path];
            }
            return $current;
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
     * @param list<string> $paths
     * @return callable(mixed[]): boolean
     */
    static function has(array $paths): callable
    {
        return function (array $record) use ($paths) {
            try {
                self::get($paths)($record);
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

    // function diffObjects( data 1, data 2) {
    //      // _.isArray は、 引数 が 配列 か どう かを 確認 する
    //       var emptyObject = _.isArray( data 1) ? [] : {}; if( data 1 === data 2) { return emptyObject; }
    //        // _.union は、 2 つ の 配列 から 一意 な 値 の 配列 を 作成 する
    //         // （数学 における 2 つ の 集合 の 和集合 と 同じ）
    //          var keys = _.union(_. keys( data 1), _.keys( data 2));
    //           return _.reduce( keys, function (acc, k) {
    //              var res = diff(_. get( data 1, k), _.get( data 2, k));
    //               // _.isObject は、 引数 が コレクション（ マップ または 配列） か どう かを 確認 し、
    // // _.isEmpty は、 引数 が 空 の コレクション か どう かを 確認 する
    //  if((_. isObject( res) && _.isEmpty( res)) ||
    //   // "no-diff" は、 2 つ の 値 が 同じ で ある こと を 示す 手段 で ある
    //    (res === "no-diff")) { return acc;
    //  } return _.set( acc, [k], res); }, emptyObject);
    //  }
    //   function diff( data 1, data 2) {
    //      // _.isObject は、 引数 が コレクション（ マップ または 配列） か どう かを 確認 する
    //       if(_. isObject( data 1) && _.isObject( data 2)) {
    //          return diffObjects( data 1, data 2);
    //          }
    //           if( data 1 !== data 2) { return data 2; }
    //            // "no-diff" は、 2 つ の 値 が 同じ で ある こと を 示す 手段
    //             return "no-diff";
    //          }
}
