<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Berkman\SlideshowBundle\Entity\Image
 */
class Image
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $id_1
     */
    private $id_1;

    /**
     * @var string $id_2
     */
    private $id_2;

    /**
     * @var string $id_3
     */
    private $id_3;

    /**
     * @var string $id_4
     */
    private $id_4;

    /**
     * @var string $id_5
     */
    private $id_5;

    /**
     * @var string $id_6
     */
    private $id_6;

    /**
     * @var boolean $public
     */
    private $public;

    /**
     * @var Berkman\SlideshowBundle\Entity\Repo
     */
    private $from_repo;

	/**
	 * Construct an image from its parts
	 *
	 * @param Berkman\SlideshowBundle\Entity\Repo $repo
	 * @param string $id1
	 * @param string $id2
	 * @param string $id3
	 * @param string $id4
	 */
	public function __construct(Repo $fromRepo, $id1, $id2 = null, $id3 = null, $id4 = null, $id5 = null, $id6 = null)
	{
		$this->setFromRepo($fromRepo);
		$this->setId1($id1);
		$this->setId2($id2);
		$this->setId3($id3);
		$this->setId4($id4);
		$this->setId5($id5);
		$this->setId6($id6);
	}

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
     * Set id
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set id_1
     *
     * @param string $id1
     */
    public function setId1($id1)
    {
        $this->id_1 = $id1;
    }

    /**
     * Get id_1
     *
     * @return string $id1
     */
    public function getId1()
    {
        return $this->id_1;
    }

    /**
     * Set id_2
     *
     * @param string $id2
     */
    public function setId2($id2)
    {
        $this->id_2 = $id2;
    }

    /**
     * Get id_2
     *
     * @return string $id2
     */
    public function getId2()
    {
        return $this->id_2;
    }

    /**
     * Set id_3
     *
     * @param string $id3
     */
    public function setId3($id3)
    {
        $this->id_3 = $id3;
    }

    /**
     * Get id_3
     *
     * @return string $id3
     */
    public function getId3()
    {
        return $this->id_3;
    }

    /**
     * Set id_4
     *
     * @param string $id4
     */
    public function setId4($id4)
    {
        $this->id_4 = $id4;
    }

    /**
     * Get id_4
     *
     * @return string $id4
     */
    public function getId4()
    {
        return $this->id_4;
    }

    /**
     * Set id_5
     *
     * @param string $id5
     */
    public function setId5($id5)
    {
        $this->id_5 = $id5;
    }

    /**
     * Get id_5
     *
     * @return string 
     */
    public function getId5()
    {
        return $this->id_5;
    }

    /**
     * Set id_6
     *
     * @param string $id6
     */
    public function setId6($id6)
    {
        $this->id_6 = $id6;
    }

    /**
     * Get id_6
     *
     * @return string 
     */
    public function getId6()
    {
        return $this->id_6;
    }

    /**
     * Set from_repo
     *
     * @param Berkman\SlideshowBundle\Entity\Repo $fromRepo
     */
    public function setFromRepo(\Berkman\SlideshowBundle\Entity\Repo $fromRepo)
    {
        $this->from_repo = $fromRepo;
    }

    /**
     * Get from_repo
     *
     * @return Berkman\SlideshowBundle\Entity\Repo $fromRepo
     */
    public function getFromRepo()
    {
        return $this->from_repo;
    }

	/**
	 * Get the metadata for this image
	 *
	 * @return array An associative array where the key is the metadata field name and value is the value
	 */

	public function getMetadata()
	{
		return $this->getFromRepo()->getFetcher()->fetchImageMetadata($this);
	}

	/**
	 * Get the full image url
	 *
	 * @return string $imageUrl
	 */
	public function getImageUrl()
	{
		return $this->getFromRepo()->getFetcher()->getImageUrl($this);
	}

	/**
	 * Get the thumbnail url 
	 *
	 * @return string $thumbnailUrl
	 */
	public function getThumbnailUrl()
	{
		return $this->getFromRepo()->getFetcher()->getThumbnailUrl($this);
	}

	/**
	 * Get the authoritative record url
	 *
	 * @return string $recordUrl
	 */
	public function getRecordUrl()
	{
		return $this->getFromRepo()->getFetcher()->getRecordUrl($this);
	}	

    public function getQRCodeUrl()
    {
        return $this->getFromRepo()->getFetcher()->getQRCodeUrl($this);
    }

    /**
     * Set public
     *
     * @param boolean $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * Get public
     *
     * @return boolean 
     */
    public function isPublic()
    {
        if (!isset($this->public)) {
            $this->public = $this->getFromRepo()->getFetcher()->isImagePublic($this);
        }
        return $this->public;
    }
}
