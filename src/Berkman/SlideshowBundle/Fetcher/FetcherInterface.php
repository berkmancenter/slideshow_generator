<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface RepoFetcherInterface {
	public function __construct(Entity\Repo $repo);
	public function getRepo();
	public function getImages($input);
	public function getMetadata($url);
	public function getNumResults($input);
}
