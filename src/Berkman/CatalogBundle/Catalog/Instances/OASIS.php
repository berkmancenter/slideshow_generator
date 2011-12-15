<?php
namespace Berkman\CatalogBundle\Catalog\Instances;

use Berkman\CatalogBundle\Catalog\Interfaces;
use Berkman\CatalogBundle\Entity\Catalog;
use Berkman\CatalogBundle\Entity\Image;

class OASIS extends Catalog implements Interfaces\ImageSearchInterface {

    /*
     * id_1 = findingAidId - e.g. sch00055
     * id_2 = nrsId - e.g. FHCL.HOUGH:2041389
     * id_3 = imageId - e.g. 2425920
     *
        $findingAidId = $image->getId1();
        $nrsId        = $image->getId2();
        $imageId      = $image->getId3();
     *
     * Notes:
     * Some things need to be urlencoded sometimes (like id-6)
     *
     */
    
    private $id = 'OASIS';
    private $name = 'Online Archival Search Information System';

    const SEARCH_URL_PATTERN    = 'http://oasistest.lib.harvard.edu:9003/solr/select?q=text:{keyword}+accesslevel:public+type:pds%20OR%20ids&start={start-index}&rows={count}';
    const RECORD_URL_PATTERN    = 'http://nrs.harvard.edu/urn-3:{id-2}';
    //const RECORD_URL_PATTERN    = 'http://oasis.lib.harvard.edu/oasis/deliver/deepLink?_imageGroup=oasis&uniqueId={id-1}';
    const METADATA_URL_PATTERN  = 'http://oasistest.lib.harvard.edu:9003/solr/select?q=accesslevel:public+type:pds%20OR%20ids+nrsid:%22{id-2}%22';
    const IMAGE_URL_PATTERN     = 'http://nrs.harvard.edu/urn-3:{id-2}?width=2400&height=2400';
    const IDS_IMAGE_URL_PATTERN     = 'http://ids.lib.harvard.edu/ids/view/{id-3}?width=2400&height=2400';
    const THUMBNAIL_URL_PATTERN = 'http://nrs.harvard.edu/urn-3:{id-2}?width=150&height=150&usethumb=y';
    const IDS_THUMBNAIL_URL_PATTERN     = 'http://ids.lib.harvard.edu/ids/view/{id-3}?width=150&height=150&usethumb=y';
    const PAGED_OBJECT_URL_PATTERN           = 'http://nrs.harvard.edu/urn-3:{id-2}?op=t';

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
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
    public function getImageMetadata(Image $image)
    {
        $metadata = array();
        $titles = array();

        $xpath = $this->fetchXpath($this->fillUrl(self::METADATA_URL_PATTERN, $image));

        for ($i = 1; $i < 6; $i++) {
            $title = $this->getNodeContent($xpath, 'objecttitle' . $i);
            if (!empty($title)) {
                $titles[] = $title;
            }
        }

        if (!empty($titles)) {
            $metadata['Title'] = implode(' — ', $titles);
        }

        $metadata['Finding Aid Title'] = $this->getNodeContent($xpath, 'maintitle');

        $metadata['Abstract'] = $this->getNodeContent($xpath, 'abstract');

        return $metadata;
    }

    /**
     * Get the full image url for a given image object
     *
     * @param Berkman\SlideshowBundle\Entity\Image @image
     * @return string $imageUrl
     */
    public function getImageUrl(Image $image)
    {
        $pattern = self::IMAGE_URL_PATTERN; 
        $imageId = $image->getId3();
        if (!empty($imageId)) {
            $pattern = self::IDS_IMAGE_URL_PATTERN;
        } 

        return $this->fillUrl($pattern, $image);
    }

    /**
     * Get the thumbnail url for a given image object
     *
     * @param Berkman\SlideshowBundle\Entity\Image @image
     * @return string $thumbnailUrl
     */
    public function getImageThumbnailUrl(Image $image)
    {
        $pattern = self::THUMBNAIL_URL_PATTERN; 
        $imageId = $image->getId3();
        if (!empty($imageId)) {
            $pattern = self::IDS_THUMBNAIL_URL_PATTERN;
        } 

        return $this->fillUrl($pattern, $image);
    }

    /**
     * Get the authoritative record url for a given image object
     *
     * @param Berkman\SlideshowBundle\Entity\Image $image
     * @return string $recordUrl
     */
    public function getImageRecordUrl(Image $image)
    {
        return $this->fillUrl(self::RECORD_URL_PATTERN, $image);
    }   

    public function getImageQRCodeUrl(Image $image)
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
        $findingAidId = $this->getNodeContent($xpath, 'recordid', $contextNode);

        // Get the NRS id
        $nrsId = $this->getNodeContent($xpath, 'nrsid', $contextNode);

        // Get the record type
        $type = $this->getNodeContent($xpath, 'type', $contextNode);

        // If it's an image, create it and add it to results
        if ($type == 'ids') {
                $image = new Image($this, $findingAidId, $nrsId);
        }
        // If it's a paged-object, we need to get the actual ID, as there doesn't appear to be
        // an NRS link directly to the image
        elseif ($type == 'pds') {
            $objectUrl = str_replace('{id-2}', $nrsId, self::PAGED_OBJECT_URL_PATTERN);
            $documentXpath = $this->fetchXpath($objectUrl, true);
            $documentXpath->registerNamespace('ns', 'http://www.w3.org/1999/xhtml');

            $imageNode = $documentXpath->query('//ns:img[contains(@src,"ids.lib.harvard.edu")]')->item(0);
            if ($imageNode) {
                $url = $imageNode->getAttribute('src');
                $matches = array();
                preg_match('!/ids/view/(\d+)\D!', $url, $matches);
                if (isset($matches[1])) {
                    $imageId = $matches[1];
                }
            }

            if (isset($imageId)) {
                $image = new Image($this, $findingAidId, $nrsId, $imageId);
            }
        }

        if (isset($image)) {

            return $image;
        }
    }
}
