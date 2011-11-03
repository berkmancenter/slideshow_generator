<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

class Fetcher {

    /**
     * Fetch an DOMXPath object for querying 
     *
     * @param string $url
     * @return DOMXPath $xpath
     */
    protected function fetchXpath($url, $followRedirects = false)
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
    protected function fillUrl($urlPattern, Entity\Image $image)
    {
        return str_replace(
            array('{id-1}', '{id-2}', '{id-3}', '{id-4}', '{id-5}', '{id-6}'),
            array($image->getId1(), $image->getId2(), $image->getId3(), $image->getId4(), $image->getId5(), $image->getId6()),
            $urlPattern
        );
    }

    /**
     * Get pages
     *
     * @param string $pagedObjectId
     * @param int $startIndex
     * @param int $endIndex
     * @return array An array of the form array('images' => $images, 'totalResults' => $totalResults)
     */
    public function getPages($pagedObjectId, $startIndex = 0, $endIndex = null)
    {
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

        $xpath = $this->fetchXpath($searchUrl);
        $linksXpath = $this->fetchXpath($linksUrl);

        $hollisLine = $linksXpath->query('//a[@class="citLinksLine"][contains(., "HOLLIS")]')->item(0);
        if ($hollisLine) {
            $hollisId = trim(substr($hollisLine->textContent, stripos('HOLLIS', $hollisLine->textContent) + 6));
        }

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
                $images[] = new Entity\Image($this->getCatalog(), $hollisId, $pageId, $imageId);
            }
        }

        return array('images' => $images, 'totalResults' => $totalResults);
    }
}
