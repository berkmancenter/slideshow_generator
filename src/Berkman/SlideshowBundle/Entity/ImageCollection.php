<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Berkman\SlideshowBundle\Entity\ImageCollection
 */
class ImageCollection
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var Berkman\SlideshowBundle\Entity\Image
     */
    private $images;

    /**
     * @var string $id_1
     */
    private $id_1;

    /**
     * @var string $id_2
     */
    private $id_2;

	/**
	 * Construct an image collection from its parts
	 *
	 * @param Berkman\SlideshowBundle\Entity\Repo $repo
	 * @param string $id1
	 * @param string $id2
	 */
	public function __construct(Repo $fromRepo, $id1, $id2 = null)
	{
		$this->setRepo($fromRepo);
		$this->setId1($id1);
		$this->setId2($id2);
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
	}
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add images
     *
     * @param Berkman\SlideshowBundle\Entity\Image $images
     */
    public function addImages(\Berkman\SlideshowBundle\Entity\Image $images)
    {
        $this->images[] = $images;
    }

    /**
     * Get images
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getImages()
    {
        return $this->images;
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
     * @return string 
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
     * @return string 
     */
    public function getId2()
    {
        return $this->id_2;
    }
    /**
     * @var Berkman\SlideshowBundle\Entity\Repo
     */
    private $repo;


    /**
     * Set repo
     *
     * @param Berkman\SlideshowBundle\Entity\Repo $repo
     */
    public function setRepo(\Berkman\SlideshowBundle\Entity\Repo $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Get repo
     *
     * @return Berkman\SlideshowBundle\Entity\Repo 
     */
    public function getRepo()
    {
        return $this->repo;
    }

	/**
	 * Get the cover image of this collection
	 *
	 * @return Berkman\SlideshowBundle\Entity\Image $image
	 */
	public function getCover()
	{
		return $this->images[0];
	}
}
