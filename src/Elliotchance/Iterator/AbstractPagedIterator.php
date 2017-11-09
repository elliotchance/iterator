<?php

namespace Elliotchance\Iterator;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;
use LogicException;
use OutOfBoundsException;

abstract class AbstractPagedIterator implements Countable, ArrayAccess, Iterator
{
    /**
     * @var array
     */
    protected $cachedPages = [];

    /**
     * @var array
     */
    protected $currentPage;

    /**
     * @var int
     */
    protected $currentPageNumber;

    /**
     * @var bool
     */
    protected $useCache = true;

    /**
     * @var integer
     */
    protected $index = 0;

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

    /**
     * @return integer
     */
    public function count()
    {
        return $this->getTotalSize();
    }

    /**
     * @param integer $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $offset >= 0 && $offset < $this->getTotalSize();
    }

    /**
     * @param integer $offset
     * @return mixed
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     */
    public function offsetGet($offset)
    {
        if (!is_int($offset)) {
            throw new InvalidArgumentException("Index must be a positive integer: $offset");
        }
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("Index out of bounds: $offset");
        }

        $page = (int) ($offset / $this->getPageSize());
        if ($this->useCache) {
            if (!array_key_exists($page, $this->cachedPages)) {
                $this->cachedPages[$page] = $this->getPage($page);
            }
            return $this->cachedPages[$page][$offset % $this->getPageSize()];
        }

        if ($page !== $this->currentPageNumber) {
            $this->currentPageNumber = $page;
            $this->currentPage = $this->getPage($page);
        }
        return $this->currentPage[$offset % $this->getPageSize()];
    }

    /**
     * @param integer $offset
     * @param mixed $value
     * @throws LogicException
     */
    public function offsetSet($offset, $value)
    {
        throw new LogicException("Setting values is not allowed.");
    }

    /**
     * @param integer $offset
     */
    public function offsetUnset($offset)
    {
        throw new LogicException("Unsetting values is not allowed.");
    }

    public function current()
    {
        return $this->offsetGet($this->index);
    }

    public function key()
    {
        return $this->index;
    }

    public function next()
    {
        ++$this->index;
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function valid()
    {
        return $this->offsetExists($this->index);
    }
}
