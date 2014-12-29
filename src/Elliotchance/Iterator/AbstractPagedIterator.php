<?php

namespace Elliotchance\Iterator;

use ArrayAccess;
use Countable;
use OutOfBoundsException;

abstract class AbstractPagedIterator implements Countable, ArrayAccess
{
    /**
     * @return integer
     */
    abstract public function getPageSize();

    /**
     * @return integer
     */
    abstract public function getTotalSize();

    /**
     * @param integer $pageNumber
     * @return array
     */
    abstract public function getPage($pageNumber);

    public function count()
    {
        return $this->getTotalSize();
    }

    public function offsetExists($offset)
    {
    }

    public function offsetGet($offset)
    {
        if ($offset < 0 || $offset > $this->getTotalSize()) {
            throw new OutOfBoundsException("Index out of bounds: $offset");
        }
        $page = ($offset / $this->getPageSize());
        return $this->getPage($page)[$offset % $this->getPageSize()];
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}
