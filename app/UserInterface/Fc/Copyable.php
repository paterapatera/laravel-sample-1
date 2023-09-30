<?php

declare(strict_types=1);

namespace App\UserInterface\Fc;

trait Copyable
{
    // 誤った推測をするので無視する
    /** @phpstan-ignore-next-line */
    function copy(...$params): self
    {
        $prev = $this->copyDefalutValues();

        return new self(...[...$prev, ...$params]);
    }

    /**
     * @return array<string, mixed>
     */
    function copyDefalutValues()
    {
        $rf = new \ReflectionClass(self::class);
        $names = array_map(fn ($v) => $v->name, $rf->getConstructor()?->getParameters() ?? []);
        $filter = array_fill_keys($names, 0);
        $defValues = (array)$this;
        return array_intersect_key($defValues, $filter);
    }

    /**
     * @template V
     * @param V $value
     * @return V
     */
    function mappingObject($value, string $key)
    {
        if ($value instanceof \Closure) {
            return $value($this->$key);
        } elseif (!is_array($value)) {
            return $value;
        } else {
            return $this->$key->merge($value);
        }
    }

    /**
     * @param array<string, mixed> $props
     */
    function merge($props): self
    {
        $data = collect($props)
            ->map($this->mappingObject(...))
            ->toArray();
        return $this->copy(...$data);
    }
}
