<?php
namespace Berkman\CatalogBundle\Entity;

use Berkman\CatalogBundle\Catalog\Interfaces;
use Berkman\CatalogBundle\Entity\Image;

abstract class Catalog {

    /**
     * Fetch an DOMXPath object for querying 
     *
     * @param string $url
     * @return DOMXPath $xpath
     */
    private $id;
    private $name;

    public function fetchXpath($url, $followRedirects = false)
    {
        $doc = new \DOMDocument();
        $doc->recover = true;
        libxml_use_internal_errors(true);
        $curlOpts = array(
            CURLOPT_RETURNTRANSFER => true, 
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 10
        );
        if ($followRedirects) {
            $curlOpts[CURLOPT_FOLLOWLOCATION] = true;
        }
        $curl = curl_init($url);
        curl_setopt_array($curl, $curlOpts);
        $response = curl_exec($curl);
        if (!$response) {
            throw new \ErrorException($url . ' did not return a valid response. (' . curl_error($curl) . ')');
        }
        $doc->loadXML($response);
        $xpath = new \DOMXPath($doc);
        return $xpath;
    }

    /**
     * Fill in the placeholders in a given URL pattern
     *
     * @param string $urlPattern
     * @param Berkman\SlideshowBundle\Entity\Image
     * @return string $url
     */
    public function fillUrl($urlPattern, Image $image)
    {
        return str_replace(
            array('{id-1}', '{id-2}', '{id-3}', '{id-4}', '{id-5}', '{id-6}'),
            array($image->getId1(), $image->getId2(), $image->getId3(), $image->getId4(), $image->getId5(), $image->getId6()),
            $urlPattern
        );
    }

    public function hasCustomImporter()
    {
        return $this instanceOf Interfaces\CustomImportInterface;
    }

    public function hasImageSearch()
    {
        return $this instanceOf Interfaces\ImageSearchInterface;
    }

    public function hasImageGroupSearch()
    {
        return $this instanceOf Interfaces\ImageGroupSearchInterface;
    }

    abstract public function getId();
    abstract public function getName();
    abstract public function importImage(array $args);
    abstract public function getImportFormat();
    abstract public function getImageUrl(Image $image);
    abstract public function getImageThumbnailUrl(Image $image);
    abstract public function getImageRecordUrl(Image $image);
    abstract public function getImageQRCodeUrl(Image $image);
    abstract public function getImageMetadata(Image $image);
}
