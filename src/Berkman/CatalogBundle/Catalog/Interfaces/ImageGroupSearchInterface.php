<?php
namespace Berkman\CatalogBundle\Interfaces;

interface ImageGroupSearchInterface {
    public function getImageGroupMetadata($imageGroup);
    public function fetchImageGroupResults($imageGroup, $startIndex, $endIndex);
}
