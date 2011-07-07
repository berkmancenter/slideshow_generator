<?php

namespace Berkman\SlideshowBundle\Parser;

use Berkman\SlideshowBundle\Entity;

interface RepoParserInterface {
	public function __construct(Entity\Repo $repo);
	public function getRepo();
	public function getImages($input);
	public function getMetadata(Entity\Image $image);
	public function getNumResults($input);
}
