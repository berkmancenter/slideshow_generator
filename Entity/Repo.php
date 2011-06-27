<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Berkman\SlideshowBundle\Entity\Repo
 */
class Repo
{
    /**
     * @var integer $id
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
     * @var Berkman\SlideshowBundle\Entity\ResultFormat
     */
    private $result_code;


    /**
     * Get id
     *
     * @return integer $id
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

    /**
     * Set result_code
     *
     * @param Berkman\SlideshowBundle\Entity\ResultFormat $resultCode
     */
    public function setResultCode(\Berkman\SlideshowBundle\Entity\ResultFormat $resultCode)
    {
        $this->result_code = $resultCode;
    }

    /**
     * Get result_code
     *
     * @return Berkman\SlideshowBundle\Entity\ResultFormat $resultCode
     */
    public function getResultCode()
    {
        return $this->result_code;
    }
    /**
     * @var string $code
     */
    private $code;


    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
