<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

/**
 * What do I want to happen here?
 *
 * This is a somewhat special object - it doesn't count as a repo, but the other repos
 * can use it to parse their results, and it speaks with the same interface
 *
 * It should receive a paged-object id
 *
 * It should return an image (the "cover" image) plus a URL
 * The URL should point to a resource that contains all the images from a paged-object
 *
 * URL: /find/{repos}/{keyword}/{paged-object-id}
 *
 * It should use the same view as search results, but it should be clear that
 * the user is looking at sub results rather than regular results, and they
 * should be able to go back easily.
 */

class PagedObjectFetcher implements FetcherInterface {

	/*
	 * id_1 = paged-object id plus page number
	 * id_2 = hollis id of paged-object
	 * id_3 = image id
	 */

	const PAGED_OBJECT_URL_PATTERN = 'http://pds.lib.harvard.edu/pds/view/{paged-object-id}?op=n&treeaction=expand&printThumbnails=true';
	const PAGED_OBJECT_LINKS_URL_PATTERN = 'http://pds.lib.harvard.edu/pds/links/{paged-object-id}';

	const RECORD_URL_PATTERN    = 'http://pds.lib.harvard.edu/pds/view/{id-1}';
	const METADATA_URL_PATTERN  = 'http://webservices.lib.harvard.edu/rest/mods/hollis/{id-2}';
	const IMAGE_URL_PATTERN     = 'http://ids.lib.harvard.edu/ids/view/{id-3}?width=2500&height=2500';
	const THUMBNAIL_URL_PATTERN = 'http://ids.lib.harvard.edu/ids/view/{id-3}?width=150&height=150&usethumb=y';

	const RESULTS_PER_PAGE = 25;

	/**
	 * @var Berkman\SlideshowBundle\Entity\Repo $repo
	 */
	private $repo;

	/**
	 * Construct the fetcher and associate with repo
	 *
	 * @param Berkman\SlideshowBundle\Entity\Repo $repo
	 */
	public function __construct(Entity\Repo $repo)
	{
		$this->repo = $repo;
	}

	/**
	 * Get the repository associated with this fetcher
	 *
	 * @return Berkman\SlideshowBundle\Entity\Repo $repo
	 */
	public function getRepo()
	{
		return $this->repo;
	}

	/**
	 * Get search results
	 *
	 * @param string $keyword
	 * @param int $startIndex
	 * @param int $endIndex
	 * @return array An array of the form array('images' => $images, 'totalResults' => $totalResults)
	 */
	public function getSearchResults($keyword, $startIndex, $endIndex)
	{
		$pagedObjectId = $keyword;
		$images = array();
		$totalResults = 0;
		$hollisId = '';
		$numResults = $endIndex - $startIndex + 1;
		$page = floor($startIndex / (self::RESULTS_PER_PAGE)) + 1;

		$searchUrl = str_replace(
			array('{paged-object-id}'),
			array($pagedObjectId), 
			self::PAGED_OBJECT_URL_PATTERN
		);

		$linksUrl = str_replace(
			array('{paged-object-id}'),
			array($pagedObjectId), 
			self::PAGED_OBJECT_LINKS_URL_PATTERN
		);

		$xml = $this->fetchXml($searchUrl);
		$linksXml = $this->fetchXml($linksUrl);

		if (!$xml || !$linksXml) {
			return array('images' => $images, 'totalResults' => 0);
		}

		libxml_use_internal_errors(true);

		$linksDoc = new \DOMDocument();
		$linksDoc->loadHTML($linksXml);
		$linksXpath = new \DOMXPath($linksDoc);
		$hollisLine = $linksXpath->query('//a[@class="citLinksLine"][contains(., "HOLLIS")]')->item(0);
		if ($hollisLine) {
			$hollisId = trim(substr($hollisLine->textContent, stripos('HOLLIS', $hollisLine->textContent) + 6));
		}

		$doc = new \DOMDocument();
		$doc->loadHTML($xml);
		$xpath = new \DOMXPath($doc);

		$links = $xpath->query('//a[@class="stdLinks"]');
		foreach ($links as $link) {
			$pageId = array();
			$imageId = array();

			preg_match('!.*/pds/view/(\d+\?n=\d+)&.*!', $link->getAttribute('href'), $pageId);
			if (isset($pageId[1])) {
				$pageId = $pageId[1];
			}
			else {
				error_log('link src: '.$link->getAttribute('href'));
			}

			$thumbnail = $xpath->query('../following-sibling::*[1]//img[@class="thumbLinks"]', $link)->item(0);
			if ($thumbnail) {
				preg_match('!http://ids\.lib\.harvard\.edu/ids/view/(\d+)\?.*!', $thumbnail->getAttribute('src'), $imageId);
				if (isset($imageId[1])) {
					$imageId = $imageId[1];
				}
				else {
					error_log('image src: '.$thumbnail->getAttrbite('src'));
				}
			}
			else {
			}

			if (!empty($hollisId) && !empty($pageId) && !empty($imageId)) {
				error_log('hollis id: '.$hollisId.' - page id: '.$pageId.' - image id: '.$imageId);
				$images[] = new Entity\Image($this->getRepo(), $hollisId, $pageId, $imageId);
			}
		}

		return array('images' => $images, 'totalResults' => $totalResults);
	}

	/**
	 * Get the metadata for a given image
	 *
	 * @param Berkman\SlideshowBundle\Entity\Image $image
	 * @return array An associative array where the key is the metadata field name and value is the value
	 */
	public function getMetadata(Entity\Image $image)
	{
		$metadata = array();
		$fields = array(
			'Title' => './/ns:unittitle',
			'Creator' => '//ns:origination[@label="creator"]',
			'Date' => './/ns:unitdate',
			'Notes' => './/ns:note'
		);
		$metadataId = $image->getId2();
		$unitId = $image->getId4();

		$metadataUrl = $this->fillUrl(self::METADATA_URL_PATTERN, $image);
		$response = $this->fetchXml($metadataUrl);
		if (!$response) {
			return array();
		}
		$doc = new \DOMDocument();
		$doc->loadXML($response);
		$xpath = new \DOMXPath($doc);
		$xpath->registerNamespace('ns', 'urn:isbn:1-931666-22-9');
		$recordContainer = $xpath->query('//ns:unitid[.="'.$unitId.'"]')->item(0);
		if ($recordContainer) {
			$recordContainer = $recordContainer->parentNode->parentNode;
			foreach ($fields as $name => $query) {
				$node = $xpath->query($query, $recordContainer)->item(0);
				if ($node) {
					$metadata[$name] = preg_replace('/\s+/', ' ', $node->textContent);
				}
			}
		}

		return $metadata;
	}

	/**
	 * Get the full image url for a given image object
	 *
	 * @param Berkman\SlideshowBundle\Entity\Image @image
	 * @return string $imageUrl
	 */
	public function getImageUrl(Entity\Image $image)
	{
		return $this->fillUrl(self::IMAGE_URL_PATTERN, $image);
	}

	/**
	 * Get the thumbnail url for a given image object
	 *
	 * @param Berkman\SlideshowBundle\Entity\Image @image
	 * @return string $thumbnailUrl
	 */
	public function getThumbnailUrl(Entity\Image $image)
	{
		return $this->fillUrl(self::THUMBNAIL_URL_PATTERN, $image);
	}

	/**
	 * Get the authoritative record url for a given image object
	 *
	 * @param Berkman\SlideshowBundle\Entity\Image $image
	 * @return string $recordUrl
	 */
	public function getRecordUrl(Entity\Image $image)
	{
		return $this->fillUrl(self::RECORD_URL_PATTERN, $image);
	}	

	/**
	 * Fetch the XML from a given url
	 *
	 * @param string $url
	 * @return string @xml
	 */
	private function fetchXml($url)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		#curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		return curl_exec($curl);
	}

	/**
	 * Fill in the placeholders in a given URL pattern
	 *
	 * @param string $urlPattern
	 * @param Berkman\SlideshowBundle\Entity\Image
	 * @return string $url
	 */
	private function fillUrl($urlPattern, Entity\Image $image)
	{
		return str_replace(
			array('{id-1}', '{id-2}', '{id-3}', '{id-4}', '{id-5}', '{id-6}'),
			array($image->getId1(), $image->getId2(), $image->getId3(), $image->getId4(), $image->getId5(), $image->getId6()),
			$urlPattern
		);
	}
}
