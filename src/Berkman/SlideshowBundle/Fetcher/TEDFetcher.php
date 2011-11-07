<?php
namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

class TEDFetcher extends Fetcher implements FetcherInterface, SearchFetcherInterface {

    /*
     * id-1 = catalogI - e.g. mcz
     * id-2 = nrsId - e.g. FMUS.MCZ:2005-562425 
     * id-3 = recordId - e.g. ARC 209-130
     *
     * Notes:
     * Some things need to be urlencoded sometimes (like id-6)
     *
     */
    
    const SEARCH_URL_PATTERN    = 'http://oasistest.lib.harvard.edu:9003/solr/select?q=system:ted+text:{keyword}+accesslevel:public+type:pds%20OR%20ids&start={start-index}&rows={count}';
    const RECORD_URL_PATTERN    = 'http://ted.lib.harvard.edu/ted/deliver/~{id-1}/{id-3}';
    const METADATA_URL_PATTERN  = 'http://oasistest.lib.harvard.edu:9003/solr/select?q=system:ted+accesslevel:public+type:pds%20OR%20ids+nrsid:%22{id-2}%22';
    const IMAGE_URL_PATTERN     = 'http://nrs.harvard.edu/urn-3:{id-2}?width=2400&height=2400';
    const THUMBNAIL_URL_PATTERN = 'http://nrs.harvard.edu/urn-3:{id-2}?width=150&height=150&usethumb=y';

    /**
     * @var Berkman\SlideshowBundle\Entity\Catalog $catalog
     */
    private $catalog;

    /**
     * Construct the fetcher and associate with catalog
     *
     * @param Berkman\SlideshowBundle\Entity\Catalog $catalog
     */
    public function __construct(Entity\Catalog $catalog)
    {
        $this->catalog = $catalog;
    }

    /**
     * Get the catalog associated with this fetcher
     *
     * @return Berkman\SlideshowBundle\Entity\Catalog $catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Get search results
     *
     * @param string $keyword
     * @param int $startIndex
     * @param int $endIndex
     * @return array An array of the form array('images' => $images, 'totalResults' => $totalResults)
     *
     * Notes:
     *    - Metadata searching is difficult as it can either come from hollis, or from within a paged-document.
     *   Still have to figure this out.
     *
     */
    public function fetchResults($keyword, $startIndex, $count)
    {
        $results      = array();
        $totalResults = 0;
        $numResults   = $count;
        $searchUrl    = str_replace(
            array('{keyword}', '{start-index}', '{count}'),
            array(urlencode($keyword), $startIndex, $numResults), 
            self::SEARCH_URL_PATTERN
        );

        // Search for the finding aids
        $xpath = $this->fetchXpath($searchUrl);
        // This doesn't make sense at this point because finding aids can contain loads of images
        $totalResults = (int) $xpath->query('//result')->item(0)->getAttribute('numFound');
        if ($totalResults < $numResults) {
            $numResults = $totalResults;
        }
        $resultNodes = $xpath->query('//doc');

        foreach ($resultNodes as $resultNode) {
            if (count($results) == $numResults) {
                break;
            }

            $results[] = $this->getImageFromNode($xpath, $resultNode);
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

        $xpath = $this->fetchXpath($this->fillUrl(self::METADATA_URL_PATTERN, $image));

        $metadata['Title'] = $this->getNodeContent($xpath, 'maintitle');

        $metadata['Abstract'] = $this->getNodeContent($xpath, 'abstract');

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

    public function getQRCodeUrl(Entity\Image $image)
    {
        return $this->getRecordUrl($image);
    }

    public function importImage(array $args)
    {
        $resourceUrl = $args[0];
        $path = parse_url($resourceUrl, PHP_URL_PATH);
        $nrsId = substr($path, strpos($path, ':') + 1);

        $url = str_replace('{id-2}', $nrsId, self::METADATA_URL_PATTERN);
        $xpath = $this->fetchXpath($url);
        
        $image = $this->getImageFromNode($xpath);

        return $image;
    }

    public function getImportFormat()
    {
        return '"Record URL (as NRS link)"';
    }

    private function getNodeContent($xpath, $nodeName, $contextNode = null)
    {
        $content = '';

        if (isset($contextNode)) {
            $node = $xpath->query('.//str[@name="' . $nodeName . '"]', $contextNode)->item(0);
        }
        else {
            $node = $xpath->query('//str[@name="' . $nodeName . '"]')->item(0);
        }

        try {
        if ($node) {
            $content = $node->textContent;
        }
        else {
            throw new \ErrorException('Could not locate node with name "' . $nodeName . '"');
        }
        } catch (\Exception $e) {

        }

        return $content;
    }

    private function getImageFromNode($xpath, $contextNode = null)
    {
        $findingAidId = null;
        $nrsId        = null;
        $imageId      = null;
        $image        = null;

        // Get the finding aid id
        $catalogId = $this->getNodeContent($xpath, 'repositorycode', $contextNode);
        
        $recordId = $this->getNodeContent($xpath, 'recordid', $contextNode);

        // Get the NRS id
        $nrsId = $this->getNodeContent($xpath, 'nrsid', $contextNode);

        // Get the record type
        $type = $this->getNodeContent($xpath, 'type', $contextNode);

        // If it's an image, create it and add it to results
        if ($type == 'ids') {
                $image = new Entity\Image($this->getCatalog(), $catalogId, $nrsId, $recordId);
        }

        if (isset($image)) {

            return $image;
        }
    }
}
