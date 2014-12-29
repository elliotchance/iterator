<?php

namespace Elliotchance\Iterator;

use ArrayAccess;
use Concise\TestCase;
use Countable;
use OutOfBoundsException;

class PagedIterator1 implements Countable, ArrayAccess
{
    public function count()
    {
        return 0;
    }

    public function offsetExists($offset)
    {
    }

    public function offsetGet($offset)
    {
        throw new OutOfBoundsException("Index out of bounds: -1");
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}

class PagedIteratorTest extends TestCase
{
    public function testCountReturnsAnInteger()
    {
        $iterator = new PagedIterator1();
        $this->assert(count($iterator), equals, 0);
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
}
