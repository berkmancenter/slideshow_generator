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

    /**
     * @var boolean $public
     */
    private $public;

    public function __construct(Repo $repo, $id1, $id2 = null, $id3 = null, $id4 = null)
    {
        $this->setRepo($repo);
        $this->setId1($id1);
        if ($id2) {
            $this->setId2($id2);
        }
        if ($id3) {
            $this->setId3($id3);
        }
        if ($id4) {
            $this->setId4($id4);
        }
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
        if ($this->images->count() < 2) {
            $images = new \Doctrine\Common\Collections\ArrayCollection();
            $results = $this->getRepo()->getFetcher()->fetchCollectionResults($this, 0, 100);
            foreach ($results['results'] as $result) {
                if ($result instanceof Image) {
                    $images[] = $result;
                }
            }
            $this->images = $images;
        }

        return $this->images;
    }

    /**
     * Get all images i.e. include images in children
     *
     * @param Berkman\SlideshowBundle\Entity\Collection $collection
     * @return array $images
     */
    public function getAllImages()
    {
        $images = $this->getImages();
        foreach ($this->getChildren() as $collection) {
            $images += $collection->getAllImages();
        }
        return $images;
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

    public function getMetadata()
    {
        return $this->repo->getFetcher()->fetchCollectionMetadata($this);
    }

    /**
     * @var string $id_3
     */
    private $id_3;

    /**
     * @var string $id_4
     */
    private $id_4;


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
     * @return string 
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
     * @return string 
     */
    public function getId4()
    {
        return $this->id_4;
    }

    /**
     * Get publicness of a collection
     *
     * @return boolean
     */
    public function isPublic()
    {
        if (!isset($this->public)) {
            $this->public = $this->getRepo()->getFetcher()->fetchCollectionPublicness($this);
        }
        return $this->public;
    }

    /**
     * Set publicness of a collection
     *
     * @param boolean $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }
}
