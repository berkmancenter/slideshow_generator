<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Berkman\SlideshowBundle\Parser as Parser;

/**
 * Berkman\SlideshowBundle\Entity\Repo
 */
class Repo
{
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $search_url_pattern
     */
    private $search_url_pattern;

    /**
     * @var string $record_url_pattern
     */
    private $record_url_pattern;

    /**
     * @var string $image_url_pattern
     */
    private $image_url_pattern;

    /**
     * @var string $metadata_url_pattern
     */
    private $metadata_url_pattern;

	/**
	 * @var Berkman\SlideshowBundle\Parser $parser
	 */
	public $parser;

    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
		$this->parser = $this->getParser(); 
    }

    /**
     * Get id
     *
     * @return string $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set search_url_pattern
     *
     * @param string $searchUrlPattern
     */
    public function setSearchUrlPattern($searchUrlPattern)
    {
        $this->search_url_pattern = $searchUrlPattern;
    }

    /**
     * Get search_url_pattern
     *
     * @return string $searchUrlPattern
     */
    public function getSearchUrlPattern()
    {
        return $this->search_url_pattern;
    }

    /**
     * Set record_url_pattern
     *
     * @param string $recordUrlPattern
     */
    public function setRecordUrlPattern($recordUrlPattern)
    {
        $this->record_url_pattern = $recordUrlPattern;
    }

    /**
     * Get record_url_pattern
     *
     * @return string $recordUrlPattern
     */
    public function getRecordUrlPattern()
    {
        return $this->record_url_pattern;
    }

    /**
     * Set image_url_pattern
     *
     * @param string $imageUrlPattern
     */
    public function setImageUrlPattern($imageUrlPattern)
    {
        $this->image_url_pattern = $imageUrlPattern;
    }

    /**
     * Get image_url_pattern
     *
     * @return string $imageUrlPattern
     */
    public function getImageUrlPattern()
    {
        return $this->image_url_pattern;
    }

    /**
     * Set metadata_url_pattern
     *
     * @param string $metadataUrlPattern
     */
    public function setMetadataUrlPattern($metadataUrlPattern)
    {
        $this->metadata_url_pattern = $metadataUrlPattern;
    }

    /**
     * Get metadata_url_pattern
     *
     * @return string $metadataUrlPattern
     */
    public function getMetadataUrlPattern()
    {
        return $this->metadata_url_pattern;
    }

	public function search($keyword, $page = 2)
	{
		$searchUrl = str_replace(array('{keyword}', '{page}'), array($keyword, $page), $this->getSearchUrlPattern());
		$curl = curl_init($searchUrl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		#curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
		$response = curl_exec($curl);
		$parser = $this->getParser();
		$images = $parser->getImages($this, $response);

		return $images;
	}

	private function getParser()
	{
		$parser = null;

		if ($this->parser) {
			$parser = $this->parser;
		}
		else {
			//TODO: figure out a better way to do this stuff
			$className = '\\Berkman\\SlideshowBundle\\Parser\\'.$this->getId().'Parser';
			if (class_exists($className)) {
				$parser = new $className();
			}
			else {
				#throw some exception
			}
		}
		return $parser;
	}
}
