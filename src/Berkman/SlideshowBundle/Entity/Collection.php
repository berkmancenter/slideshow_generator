<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Berkman\SlideshowBundle\Entity\Collection
 */
class Collection
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
     * @var Berkman\SlideshowBundle\Entity\Collection
     */
    private $children;

    /**
     * @var Berkman\SlideshowBundle\Entity\Repo
     */
    private $repo;

    /**
     * @var Berkman\SlideshowBundle\Entity\Collection
     */
    private $parent;

    /**
     * @var Berkman\SlideshowBundle\Entity\Image
     */
    private $images;

    public function __construct(Repo $repo, $id1)
    {
        $this->setRepo($repo);
        $this->setId1($id1);
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Add children
     *
     * @param Berkman\SlideshowBundle\Entity\Collection $children
     */
    public function addChildren(\Berkman\SlideshowBundle\Entity\Collection $children)
    {
        $this->children[] = $children;
    }

    /**
     * Get children
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

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
     * Set parent
     *
     * @param Berkman\SlideshowBundle\Entity\Collection $parent
     */
    public function setParent(\Berkman\SlideshowBundle\Entity\Collection $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Berkman\SlideshowBundle\Entity\Collection 
     */
    public function getParent()
    {
        return $this->parent;
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
	 * Get the cover image of this collection
	 *
	 * @return Berkman\SlideshowBundle\Entity\Image $image
	 */
	public function getCover()
	{
		return $this->images[0];
	}

    /**
     * Get the thumbnail image URL to represent this image collection (for search results form)
     *
     * @return string $url
     */
    public function getCoverUrl()
    {
        if ($this->getCover()) {
            return $this->getCover()->getThumbnailUrl();
        }
    }
}