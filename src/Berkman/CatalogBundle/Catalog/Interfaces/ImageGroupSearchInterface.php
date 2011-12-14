<?php
namespace Berkman\CatalogBundle\Catalog\Interfaces;

use Berkman\CatalogBundle\Entity\ImageGroup;

interface ImageGroupSearchInterface {
    public function getImageGroupMetadata(ImageGroup $imageGroup);
    public function fetchImageGroupResults(ImageGroup $imageGroup, $startIndex, $endIndex);
}
