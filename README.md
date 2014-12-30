iterator
========

Iterator builders for PHP

```php
use Elliotchance\Iterator\AbstractPagedIterator;

class MyPagedIterator extends AbstractPagedIterator
{
    /**
     * The total number of items we expect to find. The last page may be partial.
     * @return integer
     */
    public function getTotalSize()
    {
        return 8;
    }

    /**
     * The number of items per page. All pages must be the same size (except the
     * last page).
     * @return integer
     */
    public function getPageSize()
    {
        return 3;
    }

    /**
     * Lazy-load a specific page.
     * @return array
     */
    public function getPage($pageNumber)
    {
        $pages = [
            [ 1, 2, 3 ],
            [ 4, 5, 6 ],
            [ 7, 8 ],
        ];
        return $pages[$pageNumber];
    }
}
```

Now you can operate on it just as if were an array:

```php
$iterator = new MyPagedIterator();
echo $iterator[4]; // 5

foreach ($iterator as $item) {
    echo $item;
}
// 1 2 3 4 5 6 7 8 9
```

It's important to note that pages are cached internally after first access. This makes it ideal for
APIs that will only do one API request per page no matter what the items order requested is:

```php
use Elliotchance\Iterator\AbstractPagedIterator;

class GithubSearcher extends AbstractPagedIterator
{
    protected $totalSize = 0;
    protected $searchTerm;
    
    public function __construct($searchTerm)
    {
        $this->searchTerm = $searchTerm;
        
        // this will make sure totalSize is set before we try and access the data
        $this->getPage(0);
    }
    
    public function getTotalSize()
    {
        return $this->totalSize;
    }

    public function getPageSize()
    {
        return 100;
    }

    public function getPage($pageNumber)
    {
        $url = "https://api.github.com/search/repositories?" . http_build_query([
            'q' => 'fridge',
            'page' => $pageNumber + 1
        ]);
        $result = json_decode(file_get_contents($url), true);
        $this->totalSize = $result['total_count'];
        return $result['items'];
    }
}

$repositories = new GithubSearcher('fridge');
echo "Found " . count($repositories) . " results:\n";
foreach ($repositories as $repo) {
    echo $repo['full_name'];
}
```
