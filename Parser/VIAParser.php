<?php

namespace Berkman\SlideshowBundle\Parser;

use Berkman\SlideshowBundle\Entity;

class VIAParser extends Parser implements RepoParserInterface {

	/**
	 * @var string $repoId
	 */
	private $repoId = 'VIA';

	/**
	 * Get image objects from XML input
	 *
	 * @param string $input
	 * @return array @images
	 */
	public function getImages($input)
	{
		$images = array();

		$doc = new DOMDocument();
		$doc->loadXML($input);
		$nodeList = $doc->getElementsByTagName('item');
		foreach ($nodeList as $image) {
			$id1 = $image->getAttribute('id');
			$id2 = $image->getAttribute('hollisid');
			$fullImageUrl = $image->getElementsByTagName('fullimage')->item(0)->textContent;
			$id3 = substr($fullImageUrl, strpos($fullImageUrl, ':', 5));
			$images[] = new Image($this->repoId, $id1, $id2, $id3);
		}

		return $images;
	}

	public function getMetadata() {
	}
}
