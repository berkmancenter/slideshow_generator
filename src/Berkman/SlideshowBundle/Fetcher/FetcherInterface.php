<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface FetcherInterface {
    public function __construct(Entity\Catalog $catalog);
    public function getCatalog();
    public function getImageUrl(Entity\Image $image);
    public function getThumbnailUrl(Entity\Image $image);
    public function getRecordUrl(Entity\Image $image);
    public function getQRCodeUrl(Entity\Image $image);
    public function fetchResults($keyword, $startIndex, $count);
    public function fetchImageMetadata(Entity\Image $image);
    public function importImage(array $args);
    public function getImportFormat();
}
