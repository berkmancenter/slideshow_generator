<?php
namespace Berkman\CatalogBundle\Interfaces;

interface ImageSearchInterface {
    public function fetchResults($keyword, $startIndex, $count);
}
