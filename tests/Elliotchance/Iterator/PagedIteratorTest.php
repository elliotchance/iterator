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

class PagedIterator2 extends PagedIterator1
{
    protected $useCache = false;
}

class PagedIteratorTest extends TestCase
{
    /**
     * @var PagedIterator1
     */
    protected $cachedIterator;

    /**
     * @var PagedIterator2
     */
    protected $uncachedIterator;

    public function setUp()
    {
        parent::setUp();
        $this->cachedIterator = new PagedIterator1();
        $this->uncachedIterator = new PagedIterator2();
    }

    public function testCountReturnsAnInteger()
    {
        $this->assert(count($this->cachedIterator), equals, 8);
        $this->assert(count($this->uncachedIterator), equals, 8);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds: -1
     */
    public function testFetchingANegativeIndexThrowsAnExceptionForCachedIterator()
    {
        $this->cachedIterator[-1];
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds: -1
     */
    public function testFetchingANegativeIndexThrowsAnExceptionForUncachedIterator()
    {
        $this->uncachedIterator[-1];
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds: 15
     */
    public function testFetchingAnOutOfBoundsIndexThrowsExceptionForCachedIterator()
    {
        $this->cachedIterator[15];
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage Index out of bounds: 15
     */
    public function testFetchingAnOutOfBoundsIndexThrowsExceptionForUncachedIterator()
    {
        $this->uncachedIterator[15];
    }

    public function testAPageSizeMustBeSet()
    {
        $this->assert($this->cachedIterator->getPageSize(), equals, 3);
        $this->assert($this->uncachedIterator->getPageSize(), equals, 3);
    }

    public function testGetFirstElement()
    {
        $this->assert($this->cachedIterator[0], equals, 1);
        $this->assert($this->uncachedIterator[0], equals, 1);
    }

    public function testGetSecondElement()
    {
        $this->assert($this->cachedIterator[1], equals, 2);
        $this->assert($this->uncachedIterator[1], equals, 2);
    }

    public function testGetFirstElementOnSecondPage()
    {
        $this->assert($this->cachedIterator[3], equals, 4);
        $this->assert($this->uncachedIterator[3], equals, 4);
    }

    public function testGetSecondElementOnThirdPage()
    {
        $this->assert($this->cachedIterator[7], equals, 8);
        $this->assert($this->uncachedIterator[7], equals, 8);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Index must be a positive integer: foo
     */
    public function testFetchingAStringIndexIsNotAllowedForCachedIterator()
    {
        $this->cachedIterator['foo'];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Index must be a positive integer: foo
     */
    public function testFetchingAStringIndexIsNotAllowedUncachedIterator()
    {
        $this->uncachedIterator['foo'];
    }

    public function testOffsetOfANegativeIndexReturnsFalse()
    {
        $this->assert(isset($this->cachedIterator[-1]), is_false);
        $this->assert(isset($this->uncachedIterator[-1]), is_false);
    }

    public function testOffsetThatIsValidReturnsTrue()
    {
        $this->assert(isset($this->cachedIterator[0]), is_true);
        $this->assert(isset($this->uncachedIterator[0]), is_true);
    }

    public function testOffsetThatIsOutOfBoundsReturnsFalse()
    {
        $this->assert(isset($this->cachedIterator[15]), is_false);
        $this->assert(isset($this->uncachedIterator[15]), is_false);
    }

    public function testOffsetThatIsAfterTheLastElementReturnsFalse()
    {
        $this->assert(isset($this->cachedIterator[10]), is_false);
        $this->assert(isset($this->uncachedIterator[10]), is_false);
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

        $iterator = $this->niceMock('\Elliotchance\Iterator\PagedIterator2')
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

        $iterator = $this->niceMock('\Elliotchance\Iterator\PagedIterator2')
            ->expect('getPage')->with(0)->andReturn([1, 2, 3])
            ->with(1)->andReturn([4, 5, 6])
            ->get();

        $this->verify($iterator[0], equals, 1);
        $this->verify($iterator[3], equals, 4);
    }

    public function testValuesFromMultiplePagesAreSimultaneouslyCachedInCachedIterator()
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

    public function testValuesFromMultiplePagesAreNotSimultaneouslyCachedInUncachedIterator()
    {
        $iterator = $this->niceMock('\Elliotchance\Iterator\PagedIterator2')
            ->expect('getPage')->with(0)->twice()->andReturn([1, 2, 3])
            ->with(1)->twice()->andReturn([4, 5, 6])
            ->get();

        $this->verify($iterator[0], equals, 1);
        $this->verify($iterator[3], equals, 4);
        $this->verify($iterator[0], equals, 1);
        $this->verify($iterator[3], equals, 4);
    }

    public function testTraverseArrayInForeachLoop()
    {
        $result = [];
        foreach ($this->cachedIterator as $item) {
            $result[] = $item;
        }
        $this->assert($result, equals, [1, 2, 3, 4, 5, 6, 7, 8]);

        $result = [];
        foreach ($this->uncachedIterator as $item) {
            $result[] = $item;
        }
        $this->assert($result, equals, [1, 2, 3, 4, 5, 6, 7, 8]);
    }

    public function testTraverseArrayInMultipleForeachLoops()
    {
        $result = [];
        foreach ($this->cachedIterator as $item) {
            $result[] = $item;
        }
        foreach ($this->cachedIterator as $item) {
            $result[] = $item;
        }
        $this->assert($result, equals, [1, 2, 3, 4, 5, 6, 7, 8, 1, 2, 3, 4, 5, 6, 7, 8]);

        $result = [];
        foreach ($this->uncachedIterator as $item) {
            $result[] = $item;
        }
        foreach ($this->uncachedIterator as $item) {
            $result[] = $item;
        }
        $this->assert($result, equals, [1, 2, 3, 4, 5, 6, 7, 8, 1, 2, 3, 4, 5, 6, 7, 8]);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Setting values is not allowed.
     */
    public function testSettingAnElementThrowsAnExceptionForCachedIterator()
    {
        $this->cachedIterator[0] = true;
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Setting values is not allowed.
     */
    public function testSettingAnElementThrowsAnExceptionForUncachedIterator()
    {
        $this->uncachedIterator[0] = true;
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unsetting values is not allowed.
     */
    public function testUnsettingAnElementThrowsAnExceptionForCachedIterator()
    {
        unset($this->cachedIterator[0]);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unsetting values is not allowed.
     */
    public function testUnsettingAnElementThrowsAnExceptionForUncachedIterator()
    {
        unset($this->uncachedIterator[0]);
    }
}
