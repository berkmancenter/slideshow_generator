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

    public function __construct()
    {
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
     * @var Berkman\SlideshowBundle\Entity\Repo
     */
    private $from_repo;


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
     * @return Berkman\SlideshowBundle\Entity\Repo 
     */
    public function getFromRepo()
    {
        return $this->from_repo;
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
}
