<?php

namespace Berkman\SlideshowBundle\Parser;

use Berkman\SlideshowBundle\Entity;

interface RepoParserInterface {
	public function __construct(Entity\Repo $repo);
	public function getRepo();
	public function getImages($input);
	public function getMetadata($url);
	public function getNumResults($input);
}
