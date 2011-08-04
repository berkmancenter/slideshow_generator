<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface CollectionFetcherInterface {
	public function getImageCollectionName(Entity\ImageCollection $collection);
	public function getImageCollectionCover(Entity\ImageCollection $collection);
}
