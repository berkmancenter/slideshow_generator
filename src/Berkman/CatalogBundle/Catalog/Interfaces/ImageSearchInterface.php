<?php
namespace Berkman\CatalogBundle\Catalog\Interfaces;

interface ImageSearchInterface {
    public function fetchResults($keyword, $startIndex, $count);
}
