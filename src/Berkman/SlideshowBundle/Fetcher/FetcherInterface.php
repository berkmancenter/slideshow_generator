<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface FetcherInterface {
	public function __construct(Entity\Repo $repo);
	public function getRepo();
	public function getImageUrl(Entity\Image $image);
	public function getThumbnailUrl(Entity\Image $image);
	public function getRecordUrl(Entity\Image $image);
	public function fetchResults($keyword, $startIndex, $count);
	public function fetchImageMetadata(Entity\Image $image);
    public function isImagePublic(Entity\Image $image);
    public function importImage(array $args);
    public function getImportFormat();
    public function hasCustomImporter();
}
