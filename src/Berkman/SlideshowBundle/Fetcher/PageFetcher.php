<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

/**
 * What do I want to happen here?
 *
 * This is a somewhat special object - it doesn't count as a catalog, but the other catalogs
 * can use it to parse their results, and it speaks with the same interface
 *
 * It should receive a paged-object id
 *
 * It should return an image (the "cover" image) plus a URL
 * The URL should point to a resource that contains all the images from a paged-object
 *
 * URL: /find/{catalogs}/{keyword}/{paged-object-id}
 *
 * It should use the same view as search results, but it should be clear that
 * the user is looking at sub results rather than regular results, and they
 * should be able to go back easily.
 */

class PageFetcher extends Fetcher implements FetcherInterface {

    /*
     * id_1 = NRS id (which includes page number) e.g. FHCL.Hough:4730522?n=3
     * id_2 = PDS id
     * id_3 = hollis id of paged-object
     * id_4 = image id
     * id_5 = thumbnail id
     * id_6 = page number
     */

    const PAGED_OBJECT_URL_PATTERN = 'http://pds.lib.harvard.edu/pds/view/{paged-object-id}?op=n&treeaction=expand&printThumbnails=true';
    const PAGED_OBJECT_LINKS_URL_PATTERN = 'http://pds.lib.harvard.edu/pds/links/{paged-object-id}';

    const RECORD_URL_PATTERN    = 'http://nrs.harvard.edu/urn-3:{id-1}';
    const METADATA_URL_PATTERN  = 'http://webservices.lib.harvard.edu/rest/mods/hollis/{id-3}';
    const IMAGE_URL_PATTERN     = 'http://ids.lib.harvard.edu/ids/view/{id-4}?width=2500&height=2500';
    const THUMBNAIL_URL_PATTERN = 'http://ids.lib.harvard.edu/ids/view/{id-4}?width=150&height=150&usethumb=y';

    const RESULTS_PER_PAGE = 25;

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
     * Get the catalogsitory associated with this fetcher
     *
     * @return Berkman\SlideshowBundle\Entity\Catalog $catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
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
        $metadataId = $image->getId2();
        $unitId = $image->getId4();

        $metadataUrl = $this->fillUrl(self::METADATA_URL_PATTERN, $image);
        $xpath = $this->fetchXml($metadataUrl);
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

    public function getQRCodeUrl(Entity\Image $image)
    {
        return $this->fillUrl(self::RECORD_URL_PATTERN, $image);
    }

    public function getImportFormat()
    {
        return '"Page URL as NRS link (Check "Cite This Resource")"';
    }
    
    public function importImage(array $args)
    {
        $nrsUrl = $args[0];
        $path = parse_url($nrsUrl, PHP_URL_PATH);
        $nrsId = substr($path, strpos($path, ':') + 1);

        $xpath = $this->fetchXpath($nrsUrl, true);
        $xpath->registerNamespace('ns', 'http://www.w3.org/1999/xhtml');

        $matches = null;
        $src = $xpath->query('//ns:frame[@name="citation"]')->item(0)->getAttribute('src'); 
        preg_match('!/pds/view/(\d*)!', $src, $matches);
        $pdsId = $matches[1];

        $linksXpath = $this->fetchXpath(str_replace('{paged-object-id}', $pdsId, self::PAGED_OBJECT_LINKS_URL_PATTERN));
        $linksXpath->registerNamespace('ns', 'http://www.w3.org/1999/xhtml');
        $matches = null;
        $node = $linksXpath->query('//ns:a[@class="citLinksLine"][contains(.,"HOLLIS")]')->item(0);
        preg_match('!HOLLIS (\d*)!', $node->textContent, $matches);
        $hollisId = $matches[1];


    }
}
