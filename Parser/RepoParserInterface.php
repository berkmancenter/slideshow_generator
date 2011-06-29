<?php

namespace Berkman\SlideshowBundle\Parser;

use Berkman\SlideshowBundle\Entity\Repo as Repo;

interface RepoParserInterface {
	public function __construct(Repo $repo, $input = '');
	public function setInput($input);
	public function getRepo();
	public function getImages($input = '');
	public function getMetadata($input = '');
	public function getNumResults($input = '');
}
