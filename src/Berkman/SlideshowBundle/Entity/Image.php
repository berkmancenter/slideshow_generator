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
     * @var Berkman\SlideshowBundle\Entity\Repo
     */
    private $from_repo;


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

	public function __construct(Repo $fromRepo, $id1, $id2 = null, $id3 = null, $id4 = null)
	{
		$this->setFromRepo($fromRepo);
		$this->setId1($id1);
		$this->setId2($id2);
		$this->setId3($id3);
		$this->setId4($id4);
	}

	public function getImageUrl()
	{
		return $this->fillUrl($this->getFromRepo()->getImageUrlPattern());
	}

	public function getThumbnailUrl()
	{
		return $this->fillUrl($this->getFromRepo()->getThumbnailUrlPattern());
	}

	public function getMetadata()
	{
		return $this->getFromRepo()->getParser()->getMetadata($this->fillUrl($this->getFromRepo()->getMetadataUrlPattern()));
	}

	public function getRecordUrl()
	{
		return $this->fillUrl($this->getFromRepo()->getRecordUrlPattern());
	}

	private function fillUrl($url)
	{
		return str_replace(array('{id-1}', '{id-2}', '{id-3}', '{id-4}'), array($this->getId1(), $this->getId2(), $this->getId3(), $this->getId4()), $url);
	}
    /**
     * @var string $id_4
     */
    private $id_4;


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
	 * Convert the image to a string to be moved around
	 *
	 * @return string $image
	 */
	public function __toString() 
	{
		return base64_encode(serialize(array(
			'id' => $this->getId(),
			'id1' => $this->getId1(),
			'id2' => $this->getId2(),
			'id3' => $this->getId3(),
			'id4' => $this->getId4(),
			'fromRepo' => $this->getFromRepo()->getId()
		)));
	}
}
