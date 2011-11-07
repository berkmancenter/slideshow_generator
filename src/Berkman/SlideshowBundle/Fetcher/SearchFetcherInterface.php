<?php
namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface SearchFetcherInterface {
    public function fetchResults($keyword, $startIndex, $count);
}
