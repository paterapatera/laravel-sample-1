<?php

declare(strict_types=1);

namespace App\UserInterface\Browser;

/**
 * @template T
 * @template U
 * @method U get(T $e)
 * @method T set(T $e,U $v)
 */
class Lens
{
    /**
     * @param \Closure (T):U $get
     * @param \Closure (T,U):T $set
     */
    function __construct(public readonly \Closure $get, public readonly \Closure $set)
    {
    }

    /**
     * @param string $method
     * @param array<mixed> $args
     * @return T|U
     */
    public function __call($method, $args)
    {
        if ($method === 'get' || $method === 'set') {
            return ($this->{$method})(...$args);
        } else {
            throw new \Exception('none method');
        }
    }

    /**
     * @template A
     * @param Lens<U,A> $lens
     * @return Lens<T,A>
     */
    function to(Lens $lens): Lens
    {
        return new Lens(
            fn ($e) => ($lens->get)(($this->get)($e)),
            fn ($e, $v) => ($this->set)($e, ($lens->set)(($this->get)($e), $v))
        );
    }

    /**
     * @param T $root
     * @param \Closure(U):U $value
     * @return T
     */
    function copy($root, \Closure $value)
    {
        return $this->set($root, $value($this->get($root)));
    }

    /**
     * @template A
     * @template B
     * @template C
     * @param Lens<A,B> $lensA
     * @param Lens<B,C> $lensB
     * @return Lens<A,C>
     */
    static function pipe2(Lens $lensA, Lens $lensB): Lens
    {
        return new Lens(
            fn ($e) => ($lensB->get)(($lensA->get)($e)),
            fn ($e, $v) => ($lensA->set)($e, ($lensB->set)(($lensA->get)($e), $v))
        );
    }

    /**
     * @template A
     * @template B
     * @template C
     * @template D
     * @param Lens<A,B> $lensA
     * @param Lens<B,C> $lensB
     * @param Lens<C,D> $lensC
     * @return Lens<A,D>
     */
    static function pipe3(Lens $lensA, Lens $lensB, Lens $lensC): Lens
    {
        return Lens::pipe2(Lens::pipe2($lensA, $lensB), $lensC);
    }

    /**
     * @template A
     * @template B
     * @template C
     * @template D
     * @template E
     * @param Lens<A,B> $lensA
     * @param Lens<B,C> $lensB
     * @param Lens<C,D> $lensC
     * @param Lens<D,E> $lensD
     * @return Lens<A,E>
     */
    static function pipe4(Lens $lensA, Lens $lensB, Lens $lensC, Lens $lensD): Lens
    {
        return Lens::pipe2(Lens::pipe3($lensA, $lensB, $lensC), $lensD);
    }

    /**
     * @template A
     * @template B
     * @template C
     * @template D
     * @template E
     * @template F
     * @param Lens<A,B> $lensA
     * @param Lens<B,C> $lensB
     * @param Lens<C,D> $lensC
     * @param Lens<D,E> $lensD
     * @param Lens<E,F> $lensE
     * @return Lens<A,F>
     */
    static function pipe5(Lens $lensA, Lens $lensB, Lens $lensC, Lens $lensD, Lens $lensE): Lens
    {
        return Lens::pipe2(Lens::pipe4($lensA, $lensB, $lensC, $lensD), $lensE);
    }

    /**
     * @template A
     * @template B
     * @template C
     * @template D
     * @template E
     * @template F
     * @template G
     * @param Lens<A,B> $lensA
     * @param Lens<B,C> $lensB
     * @param Lens<C,D> $lensC
     * @param Lens<D,E> $lensD
     * @param Lens<E,F> $lensE
     * @param Lens<F,G> $lensF
     * @return Lens<A,G>
     */
    static function pipe6(Lens $lensA, Lens $lensB, Lens $lensC, Lens $lensD, Lens $lensE, Lens $lensF): Lens
    {
        return Lens::pipe2(Lens::pipe5($lensA, $lensB, $lensC, $lensD, $lensE), $lensF);
    }
}
