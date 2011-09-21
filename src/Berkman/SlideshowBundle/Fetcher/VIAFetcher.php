<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

class VIAFetcher extends Fetcher implements FetcherInterface, CollectionFetcherInterface, ImportFetcherInterface {

	/*
	 * id_1 = recordId
	 * id_2 = componentId
	 * id_3 = metadataId
	 * id_4 = metadataSubId
	 * id_5 = imageId
	 * id_6 = thumbnailId
	 */

	const SEARCH_URL_PATTERN    = 'http://webservices.lib.harvard.edu/rest/hollis/search/dc/?curpage={page}&q=format:matPhoto+branches-id:NET+{keyword}';
	const RECORD_URL_PATTERN    = 'http://via.lib.harvard.edu/via/deliver/deepLinkItem?recordId={id-1}&componentId={id-2}';
	const METADATA_URL_PATTERN  = 'http://webservices.lib.harvard.edu/rest/mods/via/{id-3}';
	const IMAGE_URL_PATTERN     = 'http://nrs.harvard.edu/urn-3:{id-5}?width=2400&height=2400';
	const THUMBNAIL_URL_PATTERN = 'http://nrs.harvard.edu/urn-3:{id-6}';

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
                    $image = new Entity\Image(
                        $this->getRepo(),
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
                        $imageCollection = new Entity\Collection($this->getRepo(), $recordId);
                        $imageCollection->addImages($image);
                        if ($imageCollection->isPublic()) {
                            $results[] = $imageCollection;
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

    public function isImagePublic(Entity\Image $image)
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

    public function isCollectionPublic(Entity\Collection $collection)
    {
        $recordId = $collection->getId1();
        $collectionUrl = str_replace('{id-3}', $recordId, self::METADATA_URL_PATTERN);
        $xpath = $this->fetchXpath($collectionUrl);
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
	 * Get the name of an image collection
	 *
	 * @param Berkman\SlideshowBundle\Entity\Collection $collection
	 * @return string $name
	 */
	public function fetchCollectionMetadata(Entity\Collection $collection)
	{
        $coverImage = $collection->getCover();
        return $coverImage->getMetadata();
	}

	/**
	 * Fetch the results from an image collection
	 *
	 * @param Berkman\SlideshowBundle\Entity\Collection $collection
	 * @return array
	 */
	public function fetchCollectionResults(Entity\Collection $collection, $startIndex = null, $endIndex = null)
	{
        $results = array();
		$recordId = $collection->getId1();
		$metadataId = $recordId;
        if (isset($startIndex, $endIndex)) {
            $numResults = $endIndex - $startIndex + 1;
        }
        $collectionUrl = str_replace('{id-3}', $recordId, self::METADATA_URL_PATTERN);

		$imageXpath = $this->fetchXpath($collectionUrl);
		$imageXpath->registerNamespace('mods', 'http://www.loc.gov/mods/v3');
		$constituents = $imageXpath->query("//mods:location");
		foreach ($constituents as $constituent) {
			if (isset($numResults) && count($results) == $numResults) {
				//break;
			}
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
                        $results[] = new Entity\Image(
                            $this->getRepo(),
                            $recordId,
                            $componentId,
                            $metadataId,
                            $metadataSubId,
                            $imageId,
                            $thumbnailId
                        );
                    }
                }
			}
		}

		return array('results' => $results, 'totalResults' => 0);
	}

    /**
     * Note: This URL will be of the RECORD_URL_PATTERN form.
     */
    public function importImage(array $args)
    {
        $url = $args[0];
        $xpath = $this->fetchXpath($url);
        $matches = array();
        $urlPattern = '!' . str_replace(array('\{id\-1\}', '\{id\-2\}'), array('(\w*)', '([:\.\w]*)'), preg_quote(self::RECORD_URL_PATTERN)) . '!';
        preg_match($urlPattern, $url, $matches);
        $recordId = $matches[1];
        $componentId = $matches[2];
        $metadataId = $recordId;
        $metadataSubId = null;
        $imageId = $componentId;
        $link = $xpath->query('//a[.="View large image"]')->item(0);
        $container = $link->parentNode->parentNode->parentNode;
        $thumbnailUrl = $xpath->query('.//img', $container)->item(0)->getAttribute('src');
        $thumbnailId = substr($thumbnailUrl, strpos($thumbnailUrl, ':', 5) + 1);
        $image = new Entity\Image(
            $this->getRepo(),
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
        return '"Record URL"';
    }

    public function getImagesFromImport(\SPLFileObject $file)
    {
        $fileContent = '';
        $images = array();
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
        foreach ($records as $record) {
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
                $images[] = $this->importImage(array(str_replace(array('{id-1}', '{id-2}'), array($viaId, $componentId), self::RECORD_URL_PATTERN)));
                error_log(str_replace(array('{id-1}', '{id-2}'), array($viaId, $componentId), self::RECORD_URL_PATTERN));
            }
        }

        return $images;
    }

    public function getImportInstructions()
    {
        return 'Download your portfolio from VIA, extract the contents from the ZIP file, and upload your Transformed_records.xml';
    }

    public function hasCustomImporter()
    {
        return true;
    }
}
