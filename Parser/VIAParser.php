<?php

namespace Berkman\SlideshowBundle\Parser;

use Berkman\SlideshowBundle\Entity;

class VIAParser implements RepoParserInterface {

	/**
	 * @var string $input
	 */
	private $input;

	/**
	 * @var Berkman\SlideshowBundle\Entity\Repo $repo
	 */
	private $repo;

	public function getRepo()
	{
		return $this->repo;
	}

	public function getInput()
	{
		return $this->input;
	}

	public function setInput($input)
	{
		$this->input = $input;
	}

	/**
	 * Get image objects from XML input
	 *
	 * @param string $input
	 * @return array @images
	 */
	public function getImages($input = '')
	{
		if ($input == '') {
			if ($this->input == '') {
				#throw some Symfony exception
			}
			else {
				$input = $this->input;
			}
		}

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
				$images[] = new Entity\Image($this->getRepo(), $id1, $id2, $id3);
			}
		}

		return $images;
	}

	public function getMetadata($input = '')
	{
	}

	public function __construct(Entity\Repo $repo, $input = '')
	{
		$this->repo = $repo;
		$this->input = $input;
	}

	public function getNumResults($input = '')
	{
	}
}
