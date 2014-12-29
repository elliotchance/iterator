<?php

namespace Elliotchance\Iterator;

use ArrayAccess;
use Concise\TestCase;
use Countable;
use OutOfBoundsException;

abstract class PagedIterator implements Countable, ArrayAccess
{
    abstract public function getPageSize();

    abstract public function getTotalSize();

    public function count()
    {
        return $this->getTotalSize();
    }

    public function offsetExists($offset)
    {
    }

    public function offsetGet($offset)
    {
        throw new OutOfBoundsException("Index out of bounds: $offset");
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}

class PagedIterator1 extends PagedIterator
{
    public function getTotalSize()
    {
        return 10;
    }

    public function getPageSize()
    {
        return 3;
    }
}

class PagedIteratorTest extends TestCase
{
    public function testCountReturnsAnInteger()
    {
        $iterator = new PagedIterator1();
        $this->assert(count($iterator), equals, 10);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds: -1
     */
    public function testFetchingANegativeIndexThrowsAnException()
    {
        $iterator = new PagedIterator1();
        $iterator[-1];
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds: 15
     */
    public function testFetchingAnOutOfBoundsIndexThrowsException()
    {
        $iterator = new PagedIterator1();
        $iterator[15];
    }

    public function testAPageSizeMustBeSet()
    {
        $iterator = new PagedIterator1();
        $this->assert($iterator->getPageSize(), equals, 3);
    }
}
