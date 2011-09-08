<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

class OASISFetcher extends Fetcher implements FetcherInterface, CollectionFetcherInterface {

	/*
     * id_1 = oasisFindingAidId - e.g. sch00055
     * id_2 = hollisId - e.g. 000605318 
	 * id_3 = imageId - e.g. 2425920
     * id_4 = unitId - e.g. 3.
	 * id_5 = pageId - e.g. 2582661?n=1
     *
     *
     * Notes:
     * The unitId corresponds to the id of the unit in the finding aid
	 */

	const FINDING_AID_XML_URL_PATTERN    = 'http://oasis.lib.harvard.edu/oasis/ead2002/schema/{finding-aid-id}';
	const PAGED_OBJECT_URL_PATTERN       = 'http://pds.lib.harvard.edu/pds/view/{paged-object-id}?op=n&treeaction=expand&printThumbnails=true';
	const PAGED_OBJECT_LINKS_URL_PATTERN = 'http://pds.lib.harvard.edu/pds/links/{paged-object-id}';

	const SEARCH_URL_PATTERN    = 'http://webservices.lib.harvard.edu/rest/hollis/search/mods/?curpage={page}&q=eadid:*+{keyword}&add_ref=612';
	const RECORD_URL_PATTERN    = 'http://oasis.lib.harvard.edu/oasis/deliver/deepLink?_collection=oasis&uniqueId={id-1}';
	const METADATA_URL_PATTERN  = 'http://oasis.lib.harvard.edu/oasis/ead2002/schema/{id-1}';
	const IMAGE_URL_PATTERN     = 'http://ids.lib.harvard.edu/ids/view/{id-3}?width=2400&height=2400';
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
     *
     * The flow for fetching results is:
     *   1. Search hollis for finding aids with that keyword and links to other resources within them
     *   2. Load the finding aid, and look for the links within in
     *   3. Follow each link, and figure out if it points to the paged document server or the image server
     *   4. Create a collection for paged documents - create an image for an image object
     *
     * Notes:
     *    - Finding aids can link to multiple pages in the same paged document, but we should only make the
     *   collection once.
     *    - Metadata searching is difficult as it can either come from hollis, or from within a paged-document.
     *   Still have to figure this out.
     *
	 */
	public function fetchResults($keyword, $page)
	{
		$results = array();
		$totalResults = 0;

		$searchUrl = str_replace(
			array('{keyword}', '{page}'),
			array(urlencode($keyword), $page), 
			self::SEARCH_URL_PATTERN
		);

        // Search for the finding aids
		$xpath = $this->fetchXpath($searchUrl);
        // This doesn't make sense at this point because finding aids can contain loads of images
		$totalResults = (int) $xpath->document->getElementsByTagName('totalResults')->item(0)->textContent;
		/*if ($totalResults < $numResults) {
			$numResults = $totalResults;
        }*/
		$noteNodes = $xpath->query('//note[@xlink:href]');

		foreach ($noteNodes as $noteNode) {

            // Get the finding aid
			$findingAidId = substr($noteNode->getAttribute('xlink:href'), -8);
			$findingAidUrl = str_replace(
				array('{finding-aid-id}'),
				array($findingAidId),
				self::FINDING_AID_XML_URL_PATTERN
			);
			$findingAidXpath = $this->fetchXpath($findingAidUrl);

            $hollisNode = $findingAidXpath->document->getElementsByTagName('eadid')->item(0);
            if ($hollisNode)
                $hollisId = $hollisNode->getAttribute('identifier');

            // Find the links in the finding aid
            // TODO: There are also apparently daoloc tags
			$imageLinkNodes = $findingAidXpath->document->getElementsByTagName('dao');
            foreach ($imageLinkNodes as $imageLinkNode) {
                // Get the unit id of the unit in the finding aid that contains the link (to get metadata later)
                $unitId = $imageLinkNode->parentNode->parentNode->parentNode->getElementsByTagName('unitid')->item(0);
                if ($unitId) {
                    $unitId = $unitId->textContent;
                }

                $imageLink = $imageLinkNode->getAttribute('xlink:href');

                // Figure out where the Name Resolution Server redirects to so we know the resource type
                // TODO: VIA is sometimes a target
                $curl = curl_init($imageLink);
                curl_setopt($curl, CURLOPT_HEADER, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                $resourceLink = array();
                // Is it a redirect to an image resource?
                if (strpos($response, 'Location: http://ids.') !== false) {
                    preg_match(
                        '!Location: http://ids\.lib\.harvard\.edu/ids/view/(\d*)\D*\\r\\n!',
                        $response,
                        $resourceLink
                    );
                    if (isset($resourceLink[1])) {
                        $imageId = $resourceLink[1];
                        $coverImage = new Entity\Image(
                            $this->getRepo(),
                            $findingAidId,
                            $hollisId,
                            $imageId,
                            $unitId
                        );
                        $collection = new Entity\Collection(
                            $this->getRepo(),
                            'findingAid',
                            $findingAidId,
                            $hollisId
                        );
                        $collection->addImages($coverImage);
                        $results[] = $collection;
                        break;
                    }
                }
                elseif (strpos($response, 'Location: http://pds.') !== false) {
                    preg_match('!Location: http://pds\.lib\.harvard\.edu/pds/view/(\d*)\D*\\r\\n!', $response, $resourceLink);
                    // If this is redirecting to a paged document (and not a page within the document)

                    /* I don't want to add pages as images to the results because a finding aid can point to both a collection
                     * and all the sub-images in a collection. If I added both the collection and the images, we'd end up with
                     * a bunch of redundant results
                     */ 
                    if (isset($resourceLink[1])) {
                        $pagedObjectId = $resourceLink[1];
                        $imageCollection = new Entity\Collection($this->getRepo(), 'pagedObject', $pagedObjectId, $unitId);
                        $collectionResult = $this->fetchCollectionResults($imageCollection, 0, 1);
                        if (!empty($collectionResult['results'])) {
                            $imageCollection->addImages($collectionResult['results'][0]);
                            $results[] = $imageCollection;
                            break;
                        }
                    }
                }
            }
		}

		return array('results' => $results, 'totalResults' => $totalResults);
	}

	/**
	 * Get the metadata for a given image
	 *
	 * @param Berkman\SlideshowBundle\Entity\Image $image
	 * @return array An associative array where the key is the metadata field name and value is the value
	 */
	public function fetchImageMetadata(Entity\Image $image)
	{
		$metadata = array();
		$fields = array(
			'Title' => './/ns:unittitle',
			'Creator' => '//ns:origination[@label="creator"]',
			'Date' => './/ns:unitdate',
			'Notes' => './/ns:note'
		);
        $imageId = $image->getId3();
		$unitId  = $image->getId4();
        $pageId  = $image->getId5();

		$metadataUrl = $this->fillUrl(self::METADATA_URL_PATTERN, $image);
		$xpath = $this->fetchXpath($metadataUrl);
		$xpath->registerNamespace('ns', 'urn:isbn:1-931666-22-9');
        if (!empty($pageId) || !empty($imageId)) {
            $links = $xpath->query('//ns:dao[@xlink:href]');
            foreach ($links as $link) {
                $curl = curl_init($link->getAttribute('xlink:href'));
                curl_setopt($curl, CURLOPT_HEADER, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                $resourceLink = array();
                // Is it a redirect to a paged object?
                if (strpos($response, 'Location: http://pds.') !== false) {
                    preg_match('!Location: http://pds\.lib\.harvard\.edu/pds/view/(\d*\?n=\d*)\D*\\r\\n!', $response, $resourceLink);
                    if (isset($resourceLink[1]) && $pageId == $resourceLink[1]) {
                        $recordContainer = $link->parentNode;
                        break;
                    }
                }
                // Is it a redirect to an image?
                if (strpos($response, 'Location: http://ids.') !== false) {
                    preg_match('!Location: http://ids\.lib\.harvard\.edu/ids/view/(\d*)\D*\\r\\n!', $response, $resourceLink);
                    if (isset($resourceLink[1]) && $imageId == $resourceLink[1]) {
                        $recordContainer = $link->parentNode;
                        break;
                    }
                }
            }
        }
        if (!isset($recordContainer)) {
            $recordContainer = $xpath->query('//ns:unitid[.="'.$unitId.'"]')->item(0);
            if ($recordContainer) {
                $recordContainer = $recordContainer->parentNode->parentNode;
            }
        }
		if ($recordContainer) {
			foreach ($fields as $name => $query) {
				$node = $xpath->query($query, $recordContainer)->item(0);
				if ($node) {
					$metadata[$name] = preg_replace('/\s+/', ' ', $node->textContent);
				}
			}
		}

		return $metadata;
	}

    public function fetchImagePublicness(Entity\Image $image)
    {

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

    public function fetchCollectionMetadata(Entity\Collection $collection)
    {

    }

    public function fetchCollectionPublicness(Entity\Collection $collection)
    {

    }

    public function fetchCollectionResults(Entity\Collection $collection, $startIndex, $endIndex)
    {
		$results = array();
		$totalResults = 0;
		$hollisId = '';
		$numResults = $endIndex - $startIndex + 1;
		$page = floor($startIndex / (self::RESULTS_PER_PAGE)) + 1;

        if ($collection->getId1() == 'findingAid') {
            // Get the finding aid
			$findingAidId = $collection->getId2();
			$findingAidUrl = str_replace(
				array('{finding-aid-id}'),
				array($findingAidId),
				self::FINDING_AID_XML_URL_PATTERN
			);
			$findingAidXpath = $this->fetchXpath($findingAidUrl);

            $hollisNode = $findingAidXpath->document->getElementsByTagName('eadid')->item(0);
            if ($hollisNode)
                $hollisId = $hollisNode->getAttribute('identifier');

            // Find the links in the finding aid
			$imageLinkNodes = $findingAidXpath->document->getElementsByTagName('dao');
            foreach ($imageLinkNodes as $imageLinkNode) {
                if (count($results) == $numResults) {
                    break;
                }

                // Get the unit id of the unit in the finding aid that contains the link (to get metadata later)
                $unitId = $imageLinkNode->parentNode->parentNode->parentNode->getElementsByTagName('unitid')->item(0);
                if ($unitId) {
                    $unitId = $unitId->textContent;
                }

                $imageLink = $imageLinkNode->getAttribute('xlink:href');

                // Figure out where the Name Resolution Server redirects to so we know the resource type
                $curl = curl_init($imageLink);
                curl_setopt($curl, CURLOPT_HEADER, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                // Is it a redirect?  Probably, because most/all links point at the NRS
                if (strpos($response, 'HTTP/1.1 303 See Other') !== false) {

                    $resourceLink = array();

                    if (strpos($response, 'Location: http://ids.') !== false) {
                        preg_match('!Location: http://ids\.lib\.harvard\.edu/ids/view/(\d*)\D*\\r\\n!', $response, $resourceLink);
                        if (isset($resourceLink[1])) {
                            $imageId = $resourceLink[1];
                            $results[] = new Entity\Image($this->getRepo(), $findingAidId, $hollisId, $imageId, $unitId);
                        }
                    }

                    if (strpos($response, 'Location: http://pds.') !== false) {
                        preg_match('!Location: http://pds\.lib\.harvard\.edu/pds/view/(\d*)(\D*)\\r\\n!', $response, $resourceLink);
                        // If this is redirecting to a paged document (and not a page within the document)

                        /* I don't want to add pages as images to the results because a finding aid can point to both a collection
                         * and all the sub-images in a collection. If I added both the collection and the images, we'd end up with
                         * a bunch of redundant results
                         */ 
                        if (isset($resourceLink[1]) && (empty($resourceLink[2]) || strpos($resourceLink[2], 'n=') === false)) {
                            $pagedObjectId = $resourceLink[1];
                            $imageCollection = new Entity\Collection($this->getRepo(), 'pagedObject', $pagedObjectId, $unitId);
                            $collectionResult = $this->fetchCollectionResults($imageCollection, 0, 1);
                            if (!empty($collectionResult['results'])) {
                                $imageCollection->addImages($collectionResult['results'][0]);
                                $results[] = $imageCollection;
                            }
                        }
                    }
                }
            }
        }
        elseif ($collection->getId1() == 'pagedObject') {
            $searchUrl = str_replace(
                array('{paged-object-id}'),
                array($collection->getId2()), 
                self::PAGED_OBJECT_URL_PATTERN
            );

            $linksUrl = str_replace(
                array('{paged-object-id}'),
                array($collection->getId2()), 
                self::PAGED_OBJECT_LINKS_URL_PATTERN
            );
            error_log('object url: ' . $searchUrl . ' - links url: ' . $linksUrl);

            $xpath = $this->fetchXpath($searchUrl);
            $xpath->registerNamespace('ns', 'http://www.w3.org/1999/xhtml');
            $linksXpath = $this->fetchXpath($linksUrl);
            $linksXpath->registerNamespace('ns', 'http://www.w3.org/1999/xhtml');

            $hollisLine = $linksXpath->query('//ns:a[@class="citLinksLine"][contains(., "HOLLIS")]')->item(0);
            if ($hollisLine) {
                $hollisId = trim(substr($hollisLine->textContent, stripos('HOLLIS', $hollisLine->textContent) + 6));
            }

            $oasisLine = $linksXpath->query('//ns:a[@class="citLinksLine"][contains(., "OASIS")]')->item(0);
            if ($oasisLine) {
                $oasisId = trim(substr($oasisLine->getAttribute('href'), -8)); 
            }

            $links = $xpath->query('//ns:a[@class="thumbLinks"]');
            foreach ($links as $link) {
                if (count($results) >= $numResults) {
                    break;
                }
                $pageId = array();
                $imageId = array();

                preg_match('!.*/pds/view/(\d+\?n=\d+)\D.*!', $link->getAttribute('href'), $pageId);
                if (isset($pageId[1])) {
                    $pageId = $pageId[1];
                }

                $thumbnail = $xpath->query('.//ns:img[@class="thumbLinks"]', $link)->item(0);
                if ($thumbnail) {
                    preg_match('!http://ids\.lib\.harvard\.edu/ids/view/(\d+)\D.*!', $thumbnail->getAttribute('src'), $imageId);
                    if (isset($imageId[1])) {
                        $imageId = $imageId[1];
                    }
                }

                if (!empty($hollisId) && !empty($pageId) && !empty($imageId) && !empty($oasisId)) {
                    $image = new Entity\Image($this->getRepo(), $oasisId, $hollisId, $imageId, $collection->getId3(), $pageId);
                    $results[] = $image;
                }
            }
        }

		return array('results' => $results, 'totalResults' => $totalResults);
	}

    public function importImage(array $args)
    {
        $findingAidUrl = $args[0];
        $recordUrl = $args[1];
    }

    public function getImportFormat()
    {
        return '"Finding Aid URL", "Record URL"';
    }
}
