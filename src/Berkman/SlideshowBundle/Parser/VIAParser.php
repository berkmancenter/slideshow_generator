<?php

namespace Berkman\SlideshowBundle\Parser;

use Berkman\SlideshowBundle\Entity;

class VIAParser implements RepoParserInterface {

	/**
	 * @var Berkman\SlideshowBundle\Entity\Repo $repo
	 */
	private $repo;

	public function getRepo()
	{
		return $this->repo;
	}

	/**
	 * Get image objects from XML input
	 *
	 * @param string $input
	 * @return array @images
	 */
	public function getImages($input)
	{
		if ($input == '') {
			#throw some Symfony exception
		}

		$images = array();

		$doc = new \DOMDocument();
		$doc->loadXML($input);
		$nodeList = $doc->getElementsByTagName('item');
		foreach ($nodeList as $image) {
			$id1 = $image->getAttribute('id');
			$id2 = $image->getAttribute('hollisid');
			$thumbnail = $image->getElementsByTagName('thumbnail')->item(0);
			if ($thumbnail) {
				$thumbnailUrl = $thumbnail->textContent;
				$id4 = substr($thumbnailUrl, strpos($thumbnailUrl, ':', 5) + 1);
			}
			$fullImage = $image->getElementsByTagName('fullimage')->item(0);
			if ($fullImage) {
				$fullImageUrl = $fullImage->textContent;
				$id3 = substr($fullImageUrl, strpos($fullImageUrl, ':', 5) + 1);
				$images[] = new Entity\Image($this->getRepo(), $id1, $id2, $id3, $id4);
			}
		}

		return $images;
	}

	public function getMetadata($url)
	{
		$metadata = array();
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		#curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		$response = curl_exec($curl);
		$doc = new \DOMDocument();
		$doc->loadXML($response);

		$fields = array(
			'title' => 'Title',
			'creator' => 'Creator',
			'description' => 'Description',
			'date' => 'Date',
			'rights' => 'Copyright'
		);

		foreach ($fields as $field => $name) {
			$nodeList = $doc->getElementsByTagNameNS('http://purl.org/dc/elements/1.1/', $field);
			if ($nodeList->length > 0) {
				$item = $nodeList->item(0);
				$metadata[$name] = $item->textContent;
			}
		}

		return $metadata;
	}

	public function __construct(Entity\Repo $repo)
	{
		$this->repo = $repo;
	}

	public function getNumResults($input)
	{
		$doc = new \DOMDocument();
		$doc->loadXML($input);
		return (int) $doc->getElementsByTagName('totalResults')->item(0)->textContent;
	}
}
