<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface ImageGroupFetcherInterface {
    public function fetchImageGroupMetadata(Entity\ImageGroup $imageGroup);
    public function fetchImageGroupResults(Entity\ImageGroup $imageGroup, $startIndex, $endIndex);
}
