<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface FetcherInterface {
	public function __construct(Entity\Repo $repo);
	public function getRepo();
	public function fetchResults($keyword, $page);
	public function fetchImageMetadata(Entity\Image $image);
    public function fetchImagePublicness(Entity\Image $image);
	public function getImageUrl(Entity\Image $image);
	public function getThumbnailUrl(Entity\Image $image);
	public function getRecordUrl(Entity\Image $image);
}
