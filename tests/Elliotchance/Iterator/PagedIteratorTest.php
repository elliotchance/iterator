<?php

namespace Elliotchance\Iterator;

use Concise\TestCase;
use OutOfBoundsException;

class PagedIterator1 extends AbstractPagedIterator
{
    protected $totalSize;
    protected $pageSize;

    public function __construct()
    {
        $this->fetchPage(0);
    }

    public function getTotalSize()
    {
        return $this->totalSize;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    protected function fetchPage($pageNumber)
    {
        $this->totalSize = 8;
        $this->pageSize = 3;

        $pages = [
            [ 1, 2, 3 ],
            [ 4, 5, 6 ],
            [ 7, 8 ],
        ];
        return $pages[$pageNumber];
    }

    public function getPage($pageNumber)
    {
        return $this->fetchPage($pageNumber);
    }
}

class PagedIteratorTest extends TestCase
{
    /**
     * @var PagedIterator1
     */
    protected $iterator;

    public function setUp()
    {
        parent::setUp();
        $this->iterator = new PagedIterator1();
    }

    public function testCountReturnsAnInteger()
    {
        $this->assert(count($this->iterator), equals, 8);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds: -1
     */
    public function testFetchingANegativeIndexThrowsAnException()
    {
        $this->iterator[-1];
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds: 15
     */
    public function testFetchingAnOutOfBoundsIndexThrowsException()
    {
        $this->iterator[15];
    }

    public function testAPageSizeMustBeSet()
    {
        $this->assert($this->iterator->getPageSize(), equals, 3);
    }

    public function testGetFirstElement()
    {
        $this->assert($this->iterator[0], equals, 1);
    }

    public function testGetSecondElement()
    {
        $this->assert($this->iterator[1], equals, 2);
    }

    public function testGetFirstElementOnSecondPage()
    {
        $this->assert($this->iterator[3], equals, 4);
    }

    public function testGetSecondElementOnThirdPage()
    {
        $this->assert($this->iterator[7], equals, 8);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Index must be a positive integer: foo
     */
    public function testFetchingAStringIndexIsNotAllowed()
    {
        $this->iterator['foo'];
    }

    public function testOffsetOfANegativeIndexReturnsFalse()
    {
        $this->assert(isset($this->iterator[-1]), is_false);
    }

    public function testOffsetThatIsValidReturnsTrue()
    {
        $this->assert(isset($this->iterator[0]), is_true);
    }

    public function testOffsetThatIsOutOfBoundsReturnsFalse()
    {
        $this->assert(isset($this->iterator[15]), is_false);
    }

    public function testOffsetThatIsAfterTheLastElementReturnsFalse()
    {
        $this->assert(isset($this->iterator[10]), is_false);
    }

    public function testValuesOfTheSameIndexAreCached()
    {
        $iterator = $this->niceMock('\Elliotchance\Iterator\PagedIterator1')
            ->expect('getPage')->with(0)->andReturn([1])
            ->get();

        $this->verify($iterator[0], equals, 1);
        $this->verify($iterator[0], equals, 1);
    }

    public function testValuesOfTheSamePagesAreCached()
    {
        $iterator = $this->niceMock('\Elliotchance\Iterator\PagedIterator1')
            ->expect('getPage')->with(0)->andReturn([1, 2])
            ->get();

        $this->verify($iterator[0], equals, 1);
        $this->verify($iterator[1], equals, 2);
    }

    public function testValuesFromAnotherPageMustBeRequested()
    {
        $iterator = $this->niceMock('\Elliotchance\Iterator\PagedIterator1')
            ->expect('getPage')->with(0)->andReturn([1, 2, 3])
                               ->with(1)->andReturn([4, 5, 6])
            ->get();

        $this->verify($iterator[0], equals, 1);
        $this->verify($iterator[3], equals, 4);
    }

    public function testValuesFromMultiplePagesAreSimultaneouslyCached()
    {
        $iterator = $this->niceMock('\Elliotchance\Iterator\PagedIterator1')
            ->expect('getPage')->with(0)->andReturn([1, 2, 3])
                               ->with(1)->andReturn([4, 5, 6])
            ->get();

        $this->verify($iterator[0], equals, 1);
        $this->verify($iterator[3], equals, 4);
        $this->verify($iterator[0], equals, 1);
        $this->verify($iterator[3], equals, 4);
    }

    public function testTraverseArrayInForeachLoop()
    {
        $result = [];
        foreach ($this->iterator as $item) {
            $result[] = $item;
        }
        $this->assert($result, equals, [1, 2, 3, 4, 5, 6, 7, 8]);
    }

    public function testTraverseArrayInMultipleForeachLoops()
    {
        $result = [];
        foreach ($this->iterator as $item) {
            $result[] = $item;
        }
        foreach ($this->iterator as $item) {
            $result[] = $item;
        }
        $this->assert($result, equals, [1, 2, 3, 4, 5, 6, 7, 8, 1, 2, 3, 4, 5, 6, 7, 8]);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Setting values is not allowed.
     */
    public function testSettingAnElementThrowsAnException()
    {
        $this->iterator[0] = true;
    }
}
