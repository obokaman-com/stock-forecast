<?php

namespace Obokaman\StockForecast\Domain\Model\Kernel;

abstract class Collection implements \Iterator, \Countable, \ArrayAccess
{
    /** @var array */
    protected $all_items = [];

    public function __construct(array $items = null)
    {
        if (null === $items)
        {
            return;
        }

        foreach ($items as $item)
        {
            $this->addItem($item);
        }
    }

    public function addItem($item)
    {
        $this->validateItem($item);

        $key = $this->getKey($item);

        if (!isset($this->all_items[$key]))
        {
            $this->all_items[$key] = $item;
        }
    }

    public function hasItem($item): bool
    {
        $this->validateItem($item);

        $key = $this->getKey($item);

        return isset($this->all_items[$key]);
    }

    public function getItem($item)
    {
        $this->validateItem($item);

        $key = $this->getKey($item);

        if (!isset($this->all_items[$key]))
        {
            throw new \RuntimeException('Item with key ' . $key . ' doesn\'t exist in ' . static::class);
        }

        return $this->all_items[$key];
    }

    public function removeItem($item)
    {
        $this->validateItem($item);

        $key = $this->getKey($item);

        if (!isset($this->all_items[$key]))
        {
            throw new \RuntimeException('Item with key ' . $key . ' doesn\'t exist in ' . static::class);
        }

        unset($this->all_items[$key]);
    }

    public function getAllItems()
    {
        return $this->all_items;
    }

    /** @return string */
    protected function getItemsClassName(): string
    {
        throw new \RuntimeException('You should redefine ' . __METHOD__ . ' when extending from Collection');
    }

    /** @return string */
    protected function getKey($item): string
    {
        throw new \RuntimeException('You should redefine ' . __METHOD__ . ' when extending from Collection');
    }

    private function validateItem($item)
    {
        $class_name = $this->getItemsClassName();

        if (null === $class_name)
        {
            return;
        }

        if ($item instanceof $class_name)
        {
            return;
        }

        throw new \InvalidArgumentException('Invalid item type. Should be instance of ' . $class_name);
    }

    public function append(Collection $a_collection)
    {
        $this->all_items += $a_collection->all_items;
    }

    public function current()
    {
        return current($this->all_items);
    }

    public function end()
    {
        return end($this->all_items);
    }

    public function next()
    {
        return next($this->all_items);
    }

    public function key()
    {
        return key($this->all_items);
    }

    public function valid()
    {
        return current($this->all_items);
    }

    public function rewind()
    {
        return reset($this->all_items);
    }

    public function count()
    {
        return count($this->all_items);
    }

    public function offsetExists($offset)
    {
        return isset($this->all_items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->all_items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->all_items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->all_items[$offset]);
    }
}
