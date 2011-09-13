<?php
namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

class OASISFetcher extends Fetcher implements FetcherInterface, CollectionFetcherInterface {

    /*
     * id_1 = findingAidId - e.g. sch00055
     * id_2 = hollisId - e.g. 000605318 
     * id_3 = imageId - e.g. 2425920
     * id_4 = unitId - e.g. 3.
     * id_5 = pageId - e.g. 2582661?n=1
     * id_6 = nrsId - e.g. FHCL.HOUGH:2041389
     *
     *
     * Notes:
     * The unitId corresponds to the id of the unit in the finding aid
     */
    
    const RESULTS_PER_PAGE      = 25;

    const SEARCH_URL_PATTERN    = 'http://webservices.lib.harvard.edu/rest/hollis/search/mods/?curpage={page}&q=eadid:*+{keyword}&add_ref=612';
    const RECORD_URL_PATTERN    = 'http://oasis.lib.harvard.edu/oasis/deliver/deepLink?_collection=oasis&uniqueId={id-1}';
    const METADATA_URL_PATTERN  = 'http://oasis.lib.harvard.edu/oasis/ead2002/schema/{id-1}';
    const IMAGE_URL_PATTERN     = 'http://ids.lib.harvard.edu/ids/view/{id-3}?width=2400&height=2400';
    const THUMBNAIL_URL_PATTERN = 'http://ids.lib.harvard.edu/ids/view/{id-3}?width=150&height=150&usethumb=y';

    const FINDING_AID_XML_URL_PATTERN            = 'http://oasis.lib.harvard.edu/oasis/ead2002/schema/{finding-aid-id}';
    const PAGED_OBJECT_URL_PATTERN               = 'http://pds.lib.harvard.edu/pds/view/{paged-object-id}?op=n&treeaction=expand&printThumbnails=true';
    const PAGED_OBJECT_RELATED_LINKS_URL_PATTERN = 'http://pds.lib.harvard.edu/pds/links/{paged-object-id}';

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
        $results      = array();
        $totalResults = 0;
        $searchUrl    = str_replace(
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
            $findingAidId = null;
            $hollisId     = null;

            // Get the finding aid
            $findingAidId    = substr($noteNode->getAttribute('xlink:href'), -8);
            $findingAidXpath = $this->fetchFindingAidXpath($findingAidId);

            $hollisNode = $findingAidXpath->document->getElementsByTagName('eadid')->item(0);
            if ($hollisNode) {
                $hollisId = $hollisNode->getAttribute('identifier');
            }

            $collection = new Entity\Collection($this->getRepo(), 'findingAid', $findingAidId, $hollisId);
            $findingAidResult = $this->fetchFindingAidResults($collection, 0, 1);
            if (isset($findingAidResult['results'][0])) {
                $result = $findingAidResult['results'][0];
                if ($result instanceof Entity\Image) {
                    $collection->addImages($result);
                    $results[] = $collection;
                }
                elseif ($result instanceof Entity\Collection) {
                    $collection->addImages($result->getCover());
                    $results[] = $collection;
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
        $fields   = array(
            'Title'   => './/ns:unittitle',
            'Creator' => '//ns:origination[@label="creator"]',
            'Date'    => './/ns:unitdate',
            'Notes'   => './/ns:note'
        );
        $findingAidId = $image->getId1();
        $hollisId     = $image->getId2();
        $imageId      = $image->getId3();
        $unitId       = $image->getId4();
        $pageId       = $image->getId5();
        $nrsId        = $image->getId6();

        $xpath = $this->fetchFindingAidXpath($findingAidId);
        $query = '//ns:dao[contains(@xlink:href,"' . $nrsId . '")]|//ns:daoloc[contains(@xlink:href,"' . $nrsId . '")]';
        $resourceNode  = $xpath->query($query)->item(0);
        $containerNode = $xpath->query('preceding::ns:did[1]', $resourceNode)->item(0);                   
        /*if (!empty($pageId) || !empty($imageId)) {
            $links = $xpath->query('//ns:dao[@xlink:href]|//ns:daoloc[@xlink:href]');
            foreach ($links as $link) {
                $url = $link->getAttribute('xlink:href');
                $resourceLink = array();
                // Is it a redirect to a paged object?
                if ($this->isDocument($url)) {
                    preg_match('!http://pds\.lib\.harvard\.edu/pds/view/(\d*\?n=\d*)\D*!', $url, $resourceLink);
                    if (isset($resourceLink[1]) && $pageId == $resourceLink[1]) {
                        $recordContainer = $link->parentNode;
                        break;
                    }
                }
                // Is it a redirect to an image?
                if ($this->isImage($url)) {
                    preg_match('!http://ids\.lib\.harvard\.edu/ids/view/(\d*)\D*!', $url, $resourceLink);
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
        }*/
        if ($containerNode) {
            foreach ($fields as $name => $query) {
                $node = $xpath->query($query, $containerNode)->item(0);
                if ($node) {
                    $metadata[$name] = preg_replace('/\s+/', ' ', $node->textContent);
                }
            }
        }

        return $metadata;
    }

    public function isImagePublic(Entity\Image $image)
    {
        $matches  = array();
        $imageUrl = $image->getImageUrl();
        return !$this->isRedirect($imageUrl);
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
        $metadata = array();
        $findingAidId = $collection->getId2();
        $hollisId = $collection->getId3();

        if ($collection->getId1() == 'findingAid') {
            $findingAidXpath = $this->fetchFindingAidXpath($findingAidId);
            $titleNode = $findingAidXpath->query('//ns:titleproper')->item(0);
            if (isset($titleNode)) {
                $metadata['Title'] = $titleNode->textContent;
            }
            $authorNode = $findingAidXpath->query('//ns:author')->item(0);
            if (isset($authorNode)) {
                $metadata['Author'] = $authorNode->textContent;
            }
        }
        elseif ($collection->getId1() == 'pagedObject') {
            $objectUrl = str_replace('{paged-object-id}', $findingAidId, self::PAGED_OBJECT_URL_PATTERN);
            $xpath = $this->fetchXpath($objectUrl);
            $xpath->registerNamespace('ns', 'http://www.w3.org/1999/xhtml');
            $citationNode = $xpath->query('//ns:div[@class="citationDiv"]')->item(0);
            if ($citationNode) {
                $metadata['Title'] = $citationNode->textContent;
            }
        }

        return $metadata;
    }

    public function isCollectionPublic(Entity\Collection $collection)
    {
        if ($collection->getId1() == 'findingAid') {
            return TRUE;
        }
        elseif ($collection->getId1() == 'pagedObject') {
            $findingAidId = $collection->getId2();
            $objectUrl = str_replace('{paged-object-id}', $findingAidId, self::PAGED_OBJECT_URL_PATTERN);
            return !$this->isRedirect($objectUrl);
        }
    }

    public function fetchCollectionResults(Entity\Collection $collection, $startIndex, $endIndex)
    {

        if ($collection->getId1() == 'findingAid') {
            $results = $this->fetchFindingAidResults($collection, $startIndex, $endIndex);
        }
        elseif ($collection->getId1() == 'pagedObject') {
            $results = $this->fetchPagedObjectResults($collection, $startIndex, $endIndex);
        }

        $totalResults = $results['totalResults'];
        $results = $results['results'];

        return array('results' => $results, 'totalResults' => $totalResults);
    }

    public function importImage(array $args)
    {
        $findingAidUrl = $args[0];
        $resourceUrl = $args[1];
        $queryVars = array();
        $image = '';

        parse_str(parse_url($findingAidUrl, PHP_URL_QUERY), $queryVars);
        $findingAidId = $queryVars['uniqueId'];
        $findingAidXpath = $this->fetchFindingAidXpath($findingAidId);

        // Get the Hollis ID
        $hollisNode = $findingAidXpath->document->getElementsByTagName('eadid')->item(0);
        if ($hollisNode) {
            $hollisId = $hollisNode->getAttribute('identifier');
        }

        // Find the node that matches the given resource URL
        $imageLinkNode = $findingAidXpath->query('//ns:*[@xlink:href="'.$resourceUrl.'"]')->item(0);

        // Get the unit id of the unit in the finding aid that contains the link (to get metadata later)
        $unitNode = $findingAidXpath->query('preceding::ns:unitid[1]', $imageLinkNode)->item(0);                   
        if ($unitNode) {
            $unitId = $unitNode->textContent;
        }

        $nrsId = $this->getNrsId($imageLinkNode->getAttribute('xlink:href'));
        
        if ($this->isImage($resourceUrl)) {
            $imageId = $this->getImageId($this->fetchUrlFromNrs($resourceUrl));
            if (isset($findingAidId, $hollisId, $imageId, $unitId, $nrsId)) {
                $image = new Entity\Image($this->getRepo(), $findingAidId, $hollisId, $imageId, $unitId, null, $nrsId);
            }
        }
        elseif ($this->isDocument($resourceUrl)) {
            $pageId = array();
            $url = $this->fetchUrlFromNrs($resourceUrl);
            preg_match('!.*/pds/view/(\d+\?n=\d+)\D*!', $url, $pageId);
            if (isset($pageId[1])) {
                $pageId = $pageId[1];
            }

            $params = array();
            parse_str(parse_url($url, PHP_URL_QUERY), $params);
            $params['op'] = 't';
            $oasisXmlUrl = substr($url, 0, strpos($url, '?') + 1) . http_build_query($params);
            $xpath = $this->fetchXpath($oasisXmlUrl);
            $xpath->registerNamespace('ns', 'http://www.w3.org/1999/xhtml');
            $imageNode = $xpath->query('//ns:img[contains(@src, "ids.lib.harvard.edu")]')->item(0);

            if (!empty($imageNode)) {
                $imageId = $this->getImageId($imageNode->getAttribute('src'));
            }

            if (!empty($pageId) && !empty($imageId)) {
                $image = new Entity\Image($this->getRepo(), $findingAidId, $hollisId, $imageId, $unitId, $pageId, $nrsId);
            }
        }

        if (!empty($image)) {
            return $image;
        }
    }

    public function getImportFormat()
    {
        return '"Finding Aid URL", "Record URL (as NRS link)"';
    }

    private function fetchPagedObjectResults(Entity\Collection $collection, $startIndex, $endIndex)
    {
        $totalResults = 0;
        $findingAidId = $collection->getId2();
        $hollisId = $collection->getId3();
        $results = array();
        $numResults = $endIndex - $startIndex + 1;
        $page = floor($startIndex / (self::RESULTS_PER_PAGE)) + 1;

        // Get the images in the paged object
        $objectUrl = str_replace('{paged-object-id}', $findingAidId, self::PAGED_OBJECT_URL_PATTERN);
        $xpath = $this->fetchXpath($objectUrl);
        $xpath->registerNamespace('ns', 'http://www.w3.org/1999/xhtml');

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
                $imageId = $this->getImageId($thumbnail->getAttribute('src'));
            }

            // Add the image if it has all the required IDs (why isn't there both a thumbnail and full image id?)
            if (isset($findingAidId, $hollisId, $imageId, $pageId)) {
                $image = new Entity\Image($this->getRepo(), $findingAidId, $hollisId, $imageId, null, $pageId, null);
                if ($this->isImagePublic($image)) {
                    $results[] = $image;
                }
            }
        }

        return array('results' => $results, 'totalResults' => $totalResults);
    }

    private function fetchFindingAidResults(Entity\Collection $collection, $startIndex, $endIndex)
    {
        $totalResults = 0;
        $results = array();

        $findingAidId = $collection->getId2();
        $hollisId = $collection->getId3();

        $numResults = $endIndex - $startIndex + 1;
        $page = floor($startIndex / (self::RESULTS_PER_PAGE)) + 1;

        // Get the finding aid
        $findingAidXpath = $this->fetchFindingAidXpath($findingAidId);

        // Find the links in the finding aid
        $imageLinkNodes = $findingAidXpath->query('//ns:dao|//ns:daoloc');
        foreach ($imageLinkNodes as $imageLinkNode) {
            $imageId = null;
            $unitId = null;
            $resourceMatches = array();
            
            if (count($results) == $numResults) {
                break;
            }

            // Get the unit id of the unit in the finding aid that contains the link (to get metadata later)
            $unitNode = $findingAidXpath->query('preceding::ns:unitid[1]', $imageLinkNode)->item(0);                   
            if ($unitNode) {
                $unitId = $unitNode->textContent;
            }

            // Get the NRS Id so we can do better metadata fetching
            $nrsId = $this->getNrsId($imageLinkNode->getAttribute('xlink:href'));

            $imageUrl = $this->fetchUrlFromNrs($imageLinkNode->getAttribute('xlink:href'));

            if ($this->isImage($imageUrl)) {
                $imageId = $this->getImageId($imageUrl);
                if (isset($findingAidId, $hollisId, $imageId, $unitId, $nrsId)) {
                    $image = new Entity\Image($this->getRepo(), $findingAidId, $hollisId, $imageId, $unitId, null, $nrsId);
                    if ($this->isImagePublic($image)) {
                        $results[] = $image;
                    }
                }
            }
            elseif ($this->isDocument($imageUrl)) {
                preg_match('!http://pds\.lib\.harvard\.edu/pds/view/(\d*)(\D*)!', $imageUrl, $resourceMatches);

                /* I don't want to add pages as images to the results because a finding aid can point to both a collection
                 * and all the sub-images in a collection. If I added both the collection and the images, we'd end up with
                 * a bunch of redundant results
                 */ 
                // If this is redirecting to a paged document (and not a page within the document)
                if (isset($resourceMatches[1]) && (empty($resourceMatches[2]) || strpos($resourceMatches[2], 'n=') === false)) {
                    $pagedObjectId = $resourceMatches[1];
                    $imageCollection = new Entity\Collection($this->getRepo(), 'pagedObject', $pagedObjectId, $unitId);
                    if ($this->isCollectionPublic($imageCollection)) {
                        $collectionResult = $this->fetchCollectionResults($imageCollection, 0, 1);
                        if (!empty($collectionResult['results'])) {
                            $imageCollection->addImages($collectionResult['results'][0]);
                            $results[] = $imageCollection;
                        }
                    }
                }
            }
        }

        return array('results' => $results, 'totalResults' => $totalResults);
    }

    private function isImage($url)
    {
        if (strpos($url, 'http://nrs.harvard.edu') !== false) {
            $url = $this->fetchUrlFromNrs($url);
        }

        return strpos($url, 'http://ids.lib.harvard.edu') !== false;
    }

    private function isDocument($url)
    {
        if (strpos($url, 'http://nrs.harvard.edu') !== false) {
            $url = $this->fetchUrlFromNrs($url);
        }

        return strpos($url, 'http://pds.lib.harvard.edu') !== false;
    }

    private function fetchUrlFromNrs($url)
    {
        if (strpos($url, 'nrs.harvard.edu') === false) {
            return $url;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        $matches = array();
        preg_match('!Location: (http://.*)\\r\\n!', $response, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        else {
            //throw new \ErrorException($url . ' was not a parsable redirect.');
            return false;
        }
    }

    private function fetchFindingAidXpath($findingAidId)
    {
        $findingAidUrl = str_replace('{finding-aid-id}', $findingAidId, self::FINDING_AID_XML_URL_PATTERN);
        $findingAidXpath = $this->fetchXpath($findingAidUrl);
        $findingAidXpath->registerNamespace('ns', 'urn:isbn:1-931666-22-9');
        return $findingAidXpath;
    }

    private function getNrsId($url)
    {
        $matches = array();
        $nrsId = null;
        preg_match(
           '!http://nrs\.harvard\.edu/urn\-3:([\.\:\w]*)!',
            $url,
            $matches
        ); 
        if (isset($matches[1])) {
            $nrsId = $matches[1];
        }
        return $nrsId;
    }

    private function getImageId($url)
    {
        $matches = array();
        $imageId = null;
        preg_match('!http://ids\.lib\.harvard\.edu/ids/view/(\d*)\D*!', $url, $matches);
        if (isset($matches[1])) {
            $imageId = $matches[1];
        }

        return $imageId;
    }

    private function isRedirect($url)
    {
        $curl     = curl_init($url);
        curl_setopt_array($curl, array(
            CURLOPT_NOBODY         => true,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true
        ));
        $response = curl_exec($curl);

        return strpos($response, 'HTTP/1.1 302') !== false;
    }

    public function hasCustomImporter()
    {
        return false;
    }
}
