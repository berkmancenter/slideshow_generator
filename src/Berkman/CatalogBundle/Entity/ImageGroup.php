<?php
namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Berkman\CatalogBundle\Entity\Catalog;

/**
 * Berkman\SlideshowBundle\Entity\ImageGroup
 */
class ImageGroup
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
     * @var Berkman\SlideshowBundle\Entity\ImageGroup
     */
    private $children;

    /**
     * @var Berkman\SlideshowBundle\Entity\Catalog
     */
    private $catalog;

    /**
     * @var Berkman\SlideshowBundle\Entity\ImageGroup
     */
    private $parent;

    /**
     * @var Berkman\SlideshowBundle\Entity\Image
     */
    private $images;

    public function __construct(Catalog $catalog, $id1, $id2 = null, $id3 = null, $id4 = null)
    {
        $this->setCatalog($catalog);
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
     * Add children
     *
     * @param Berkman\SlideshowBundle\Entity\ImageGroup $children
     */
    public function addChildren(ImageGroup $children)
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
     * Set catalog
     *
     * @param Berkman\SlideshowBundle\Entity\Catalog $catalog
     */
    public function setCatalog(Catalog $catalog)
    {
        $this->catalog = $catalog;
    }

    /**
     * Get catalog
     *
     * @return Berkman\SlideshowBundle\Entity\Catalog 
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Set parent
     *
     * @param Berkman\SlideshowBundle\Entity\ImageGroup $parent
     */
    public function setParent(ImageGroup $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     *
     * @return Berkman\SlideshowBundle\Entity\ImageGroup 
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
    public function addImages(Image $images)
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
            $results = $this->getCatalog()->getFetcher()->fetchImageGroupResults($this, 0, 100);
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
     * @param Berkman\SlideshowBundle\Entity\ImageGroup $imageGroup
     * @return array $images
     */
    public function getAllImages()
    {
        $images = $this->getImages();
        foreach ($this->getChildren() as $imageGroup) {
            $images += $imageGroup->getAllImages();
        }
        return $images;
    }

    /**
     * Get the cover image of this imageGroup
     *
     * @return Berkman\SlideshowBundle\Entity\Image $image
     */
    public function getCover()
    {
        return $this->images[0];
    }

    /**
     * Pass getters and issers off to the catalog
     */
    public function __call($functionName, $arguments)
    {
        $nameArray = preg_split('/([[:upper:]][[:lower:]]+)/', $functionName, null, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY)
        $functionName = $nameArray[0] . 'ImageGroup' . array_slice($nameArray, 1);
        return call_user_func_array(array($this->getCatalog(), $functionName), $arguments);
}
