<?php

namespace Berkman\SlideshowBundle\Parser;

use Berkman\SlideshowBundle\Entity;

class VIAParser implements RepoParserInterface {

	/**
	 * @var string $repoId
	 */

	/**
	 * Get image objects from XML input
	 *
	 * @param string $input
	 * @return array @images
	 */
	public function getImages($repo, $input)
	{
		$images = array();

		$doc = new \DOMDocument();
		$doc->loadXML($input);
		$nodeList = $doc->getElementsByTagName('item');
		foreach ($nodeList as $image) {
			$id1 = $image->getAttribute('id');
			$id2 = $image->getAttribute('hollisid');
			$fullImage = $image->getElementsByTagName('fullimage')->item(0);
			if ($fullImage) {
				$fullImageUrl = $fullImage->textContent;
				$id3 = substr($fullImageUrl, strpos($fullImageUrl, ':', 5) + 1);
				$images[] = new Entity\Image($repo, $id1, $id2, $id3);
			}
		}

		return $images;
	}

	public function getMetadata() {
	}
}
