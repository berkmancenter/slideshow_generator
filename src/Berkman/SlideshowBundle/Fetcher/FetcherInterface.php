<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface RepoFetcherInterface {
	public function __construct(Entity\Repo $repo);
	public function getRepo();
	public function getSearchResults(string $keyword, int $startIndex, int $endIndex);
	public function getMetadata(Entity\Image $image);
	public function getImageUrl(Entity\Image $image);
	public function getThumbnailUrl(Entity\Image $image);
	public function getRecordUrl(Entity\Image $image);
}
