<?php

namespace Berkman\SlideshowBundle\Parser;

interface RepoParserInterface {
	public function getImages();
	public function getMetadata();
}
