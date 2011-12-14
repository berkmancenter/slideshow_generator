<?php
namespace Berkman\CatalogBundle\Catalog\Instances;

use Berkman\CatalogBundle\Catalog\Catalog;
use Berkman\CatalogBundle\Catalog\Interfaces;
use Berkman\CatalogBundle\Entity\Image;
use Berkman\CatalogBundle\Entity\ImageGroup;

class VIA extends Catalog implements Interfaces\ImageGroupSearchInterface, Interfaces\CustomImportInterface, Interfaces\ImageSearchInterface {

    /*
     * id_1 = recordId
     * id_2 = componentId
     * id_3 = metadataId
     * id_4 = metadataSubId
     * id_5 = imageId
     * id_6 = thumbnailId
     */
    const NAME = 'Visual Information Access';
    const ID = 'VIA';

    const SEARCH_URL_PATTERN    = 'http://webservices.lib.harvard.edu/rest/hollis/search/dc/?curpage={page}&q=format:matPhoto+branches-id:NET+{keyword}';
    const RECORD_URL_PATTERN    = 'http://via.lib.harvard.edu/via/deliver/deepLinkItem?recordId={id-1}&componentId={id-2}';
    const METADATA_URL_PATTERN  = 'http://webservices.lib.harvard.edu/rest/mods/via/{id-3}';
    const IMAGE_URL_PATTERN     = 'http://nrs.harvard.edu/urn-3:{id-5}?width=2400&height=2400';
    const THUMBNAIL_URL_PATTERN = 'http://nrs.harvard.edu/urn-3:{id-6}';
    const QR_CODE_URL_PATTERN   = 'http://m.harvard.edu/libraries/detail?id=viaid%3A{id-1}';


    public function getId()
    {
        return self::ID;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * Get search results
     *
     * @param string $keyword
     * @param int $startIndex
     * @param int $endIndex
     * @return array An array of the form array('images' => $images, 'totalResults' => $totalResults)
     */
    public function fetchResults($keyword, $startIndex, $count)
    {
        $results = array();
        $totalResults = 0;

        $page = floor($startIndex / 25) + 1;

        $searchUrl = str_replace(
            array('{keyword}', '{page}'),
            array(urlencode($keyword), $page), 
            self::SEARCH_URL_PATTERN
        );
        
        $xpath = $this->fetchXpath($searchUrl);
        $totalResults = (int) $xpath->document->getElementsByTagName('totalResults')->item(0)->textContent;
        $nodeList = $xpath->document->getElementsByTagName('item');
        $ii = $nodeList->length;
        for ($i = $startIndex % 25; $i < $ii; $i++) {
            if (count($results) == $count) {
                break;
            }
            $image = $nodeList->item($i);

            $recordId = $image->getAttribute('hollisid');
            $metadataId = $recordId;
            $componentId = null;
            $metadataSubId = null;
            $imageId = null;
            $thumbnailId = null;

            $numberOfImages = $image->getElementsByTagName('numberofimages')->item(0);

            if ($numberOfImages && $numberOfImages->textContent >= 1) {
                $thumbnail = $image->getElementsByTagName('thumbnail')->item(0);
                $fullImage = $image->getElementsByTagName('fullimage')->item(0);

                if ($thumbnail && $fullImage) {
                    $thumbnailUrl = $thumbnail->textContent;
                    $thumbnailId = substr($thumbnailUrl, strpos($thumbnailUrl, ':', 5) + 1);
                    $fullImageUrl = $fullImage->textContent;
                    $componentId = substr($fullImageUrl, strpos($fullImageUrl, ':', 5) + 1);
                    $imageId = $componentId;
                    $image = new Image(
                        $this,
                        $recordId,
                        $componentId,
                        $metadataId,
                        $metadataSubId,
                        $imageId,
                        $thumbnailId
                    );
                    if ($numberOfImages->textContent == 1 && $image->isPublic()) {
                        $results[] = $image;
                    } 
                    else {
                        $imageGroup = new ImageGroup($this, $recordId);
                        $imageGroup->addImages($image);
                        if ($imageGroup->isPublic()) {
                            $results[] = $imageGroup;
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
    public function getImageMetadata(Image $image)
    {
        $metadata = array();
        $fields = array(
            'Title' => './/mods:title',
            'Creator' => './/mods:namePart[1]',
            'Date' => './/mods:dateCreated[last()]',
            //'Usage Restrictions' => './/mods:accessCondition',
            'Notes' => './mods:note'
        );
        $metadataId = $image->getId3();
        if ($image->getId4()) {
            $metadataId = $image->getId4();
        }
        $metadataUrl = $this->fillUrl(self::METADATA_URL_PATTERN, $image);
        $xpath = $this->fetchXpath($metadataUrl);
        $xpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3');
        $recordIdent = $xpath->query("//mods:recordIdentifier[.='".$metadataId."']")->item(0);
        if ($recordIdent) {
            $recordContainer = $recordIdent->parentNode->parentNode;
            
            foreach ($fields as $name => $query) {
                $node = $xpath->query($query, $recordContainer)->item(0);
                if ($node) {
                    $metadata[$name] = $node->textContent;
                }
            }
        }

        return $metadata;
    }

    public function isImagePublic(Image $image)
    {
        $public = false;
        $xpath = $this->fetchXpath($this->fillUrl(self::METADATA_URL_PATTERN, $image));
        $xpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3');
        $imageNodes = $xpath->query("//mods:url[@displayLabel='Full Image'][contains(.,'" . $image->getId5() . "')]");
        if ($imageNodes->item(0) && $imageNodes->item(0)->getAttribute('note') == 'unrestricted') {
            $public = true;
        }
        return $public;
    }

    public function isImageGroupPublic(ImageGroup $imageGroup)
    {
        $recordId = $imageGroup->getId1();
        $imageGroupUrl = str_replace('{id-3}', $recordId, self::METADATA_URL_PATTERN);
        $xpath = $this->fetchXpath($imageGroupUrl);
        $xpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3');
        $publicImages = $xpath->query(".//mods:url[@displayLabel='Full Image'][@note='unrestricted']");
        return $publicImages->length > 0;
    }

    /**
     * Get the full image url for a given image object
     *
     * @param Berkman\SlideshowBundle\Entity\Image @image
     * @return string $imageUrl
     */
    public function getImageUrl(Image $image)
    {
        return $this->fillUrl(self::IMAGE_URL_PATTERN, $image);
    }

    /**
     * Get the thumbnail url for a given image object
     *
     * @param Berkman\SlideshowBundle\Entity\Image @image
     * @return string $thumbnailUrl
     */
    public function getImageThumbnailUrl(Image $image)
    {
        return $this->fillUrl(self::THUMBNAIL_URL_PATTERN, $image);
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
        return $this->fillUrl(self::QR_CODE_URL_PATTERN, $image);
    }

    /**
     * Get the name of an image imageGroup
     *
     * @param Berkman\SlideshowBundle\Entity\ImageGroup $imageGroup
     * @return string $name
     */
    public function getImageGroupMetadata(ImageGroup $imageGroup)
    {
        $coverImage = $imageGroup->getCover();
        return $coverImage->getMetadata();
    }

    /**
     * Fetch the results from an image imageGroup
     *
     * @param Berkman\SlideshowBundle\Entity\ImageGroup $imageGroup
     * @return array
     */
    public function fetchImageGroupResults(ImageGroup $imageGroup, $startIndex = null, $count = null)
    {
        $results = array();
        $recordId = $imageGroup->getId1();
        $totalResults = 0;
        $i = -1;
        $metadataId = $recordId;
        $imageGroupUrl = str_replace('{id-3}', $recordId, self::METADATA_URL_PATTERN);

        $imageXpath = $this->fetchXpath($imageGroupUrl);
        $imageXpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3');
        $constituents = $imageXpath->query("//mods:location");
        foreach ($constituents as $constituent) {
            $i++;
            $fullImage = $imageXpath->query(".//mods:url[@displayLabel='Full Image'][@note='unrestricted']", $constituent)->item(0);
            if ($fullImage) {
                $componentId = substr($fullImage->textContent, strpos($fullImage->textContent, ':', 5) + 1);
                $imageId = $componentId;
                $thumbnail = $imageXpath->query(".//mods:url[@displayLabel='Thumbnail']", $constituent)->item(0);
                if ($thumbnail) {
                    $thumbnailId = substr($thumbnail->textContent, strpos($thumbnail->textContent, ':', 5) + 1).'?height=150&width=150';
                    $recordIdentifier = $imageXpath->query('.//mods:recordIdentifier', $constituent->parentNode)->item(0);
                    $metadataSubId = $recordIdentifier->textContent;
                    if (!empty($thumbnailId)) {
                        if ($i >= $startIndex && count($results) < $count) {
                            $results[] = new Image(
                                $this,
                                $recordId,
                                $componentId,
                                $metadataId,
                                $metadataSubId,
                                $imageId,
                                $thumbnailId
                            );
                        }
                        $totalResults++;
                    }
                }
            }
        }

        return array('results' => $results, 'totalResults' => $totalResults);
    }

    /**
     * Note: This URL will be of the RECORD_URL_PATTERN form.
     */
    public function importImage(array $args)
    {
        $url = $args[0];
        $xpath = $this->fetchXpath($url);
        $args = array();
        parse_str(parse_url($url, PHP_URL_QUERY), $args);
        $recordId = $args['recordId'];
        $componentId = $args['componentId'];
        $metadataId = $recordId;
        $metadataSubId = null;
        $imageId = $componentId;
        $link = $xpath->query('//a[.="View large image"]')->item(0);
        $container = $link->parentNode->parentNode->parentNode;
        $thumbnailUrl = $xpath->query('.//img', $container)->item(0)->getAttribute('src');
        $thumbnailId = substr($thumbnailUrl, strpos($thumbnailUrl, ':', 5) + 1);
        $image = new Image(
            $this,
            $recordId,
            $componentId,
            $metadataId,
            $metadataSubId,
            $imageId,
            $thumbnailId
        );
        return $image;
    }

    public function getImportFormat()
    {
        return '"Bookmark URL"';
    }

    public function getImagesFromImport($file)
    {
        $fileContent = '';
        $images = array();
        $failed = 0;
        while (!$file->eof()) {
            $fileContent .= $file->fgets();
        }

        $doc = new \DOMDocument();
        $doc->recover = true;
        libxml_use_internal_errors(true);
        $doc->loadXML($fileContent);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('ns', 'http://hul.harvard.edu/ois/xml/xsd/via/newvia_export.xsd');
        $records = $xpath->query('//ns:record');
        $totalRecords = $records->length;
        $count = 0;
        foreach ($records as $record) {
            $batch->setProgress(round($count / $totalRecords * 100));
            $count++;
            $viaId = null;
            $componentId = null;
            $restricted = false;
            $viaNode = $xpath->query('.//ns:via_id', $record)->item(0);
            if ($viaNode) {
                $viaId = $viaNode->textContent;
            }
            $imageLinkNode = $xpath->query('.//ns:imagelink', $record)->item(0);
            if ($imageLinkNode) {
                $componentId = substr($imageLinkNode->textContent, strpos($imageLinkNode->textContent, ':', 5) + 1);
            }
            $restrictedNode = $xpath->query('.//ns:restricted_image', $record)->item(0);
            if ($restrictedNode) {
                $restricted = ($restrictedNode->textContent == 'true') ? true : false;
            }
            if (isset($viaId, $componentId) && !$restricted) {
                try { 
                    $images[] = $this->importImage(array(str_replace(array('{id-1}', '{id-2}'), array($viaId, $componentId), self::RECORD_URL_PATTERN)));
                } catch (\ErrorException $e) {
                    $failed++;
                    continue;
                }
            }
        }

        return $images;
    }

    public function getImportInstructions()
    {
        return 'Download your portfolio from VIA, extract the contents from the ZIP file, and upload your Transformed_records.xml';
    }
}
