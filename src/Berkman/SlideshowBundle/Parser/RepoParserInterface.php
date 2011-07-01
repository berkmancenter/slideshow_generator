<?php

namespace Berkman\SlideshowBundle\Parser;

use Berkman\SlideshowBundle\Entity\Repo as Repo;

interface RepoParserInterface {
	public function __construct(Repo $repo);
	public function setInput($input);
	public function getRepo();
	public function getImages();
	public function getMetadata();
	public function getNumResults();
}
