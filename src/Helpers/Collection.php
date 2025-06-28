<?php

declare(strict_types=1);

namespace GoogleDrivePHP\Helpers;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;

if (!function_exists('GoogleDrivePHP\Helpers\collect')) {
    /**
     * Create a collection from the given value.
     *
     * @param mixed $value
     * @return SimpleCollection|\Illuminate\Support\Collection
     */
    function collect($value = null)
    {
        if (class_exists('\Illuminate\Support\Collection')) {
            return new \Illuminate\Support\Collection($value);
        }
        
        // Fallback simple collection implementation
        return new SimpleCollection($value);
    }
}

/**
 * Simple collection implementation as fallback
 */
class SimpleCollection implements ArrayAccess, Countable, IteratorAggregate
{
    /** @var array<int|string, mixed> */
    protected array $items = [];

    /**
     * @param mixed $items
     */
    public function __construct($items = [])
    {
        $this->items = is_array($items) ? $items : [$items];
    }

    /**
     * @param callable $callback
     * @return self
     */
    public function map(callable $callback): self
    {
        return new static(array_map($callback, $this->items));
    }

    /**
     * @param callable|null $callback
     * @return self
     */
    public function filter(callable $callback = null): self
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback));
        }
        return new static(array_filter($this->items));
    }

    /**
     * @return array<int|string, mixed>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param int|string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    /**
     * @param int|string $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->items[$offset];
    }

    /**
     * @param int|string|null $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /**
     * @param int|string $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * @return ArrayIterator<int|string, mixed>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
