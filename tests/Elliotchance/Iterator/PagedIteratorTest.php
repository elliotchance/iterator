<?php

namespace Elliotchance\Iterator;

use Concise\TestCase;
use Countable;

class PagedIterator1 implements Countable
{
    public function count()
    {
        return 0;
    }
}

class PagedIteratorTest extends TestCase
{
    public function testCountReturnsAnInteger()
    {
        $iterator = new PagedIterator1();
        $this->assert(count($iterator), equals, 0);
    }
}
