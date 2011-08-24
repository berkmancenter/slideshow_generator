<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface CollectionFetcherInterface {
	public function fetchCollectionMetadata(Entity\Collection $collection);
	public function fetchCollectionResults(Entity\Collection $collection, $startIndex, $endIndex);
}
