<?php

namespace Berkman\SlideshowBundle\Parser;

interface RepoParserInterface {
	public function getImages($repo, $input);
	public function getMetadata();
}
