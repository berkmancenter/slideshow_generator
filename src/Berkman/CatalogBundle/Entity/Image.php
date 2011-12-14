<?php
namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Berkman\CatalogBundle\Entity\Catalog;

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
     * @var Berkman\SlideshowBundle\Entity\Catalog
     */
    private $catalog;

    /**
     * Construct an image from its parts
     *
     * @param Berkman\SlideshowBundle\Entity\Catalog $catalog
     * @param string $id1
     * @param string $id2
     * @param string $id3
     * @param string $id4
     */
    public function __construct(Catalog $catalog, $id1, $id2 = null, $id3 = null, $id4 = null, $id5 = null, $id6 = null)
    {
        $this->setCatalog($catalog);
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
     * Set from_catalog
     *
     * @param Berkman\SlideshowBundle\Entity\Catalog $fromCatalog
     */
    public function setCatalog(Catalog $catalog)
    {
        $this->catalog = $catalog;
    }

    /**
     * Get from_catalog
     *
     * @return Berkman\SlideshowBundle\Entity\Catalog $fromCatalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }

    /**
     * Pass getters and issers off to the catalog
     */
    public function __call($functionName, $arguments)
    {
        $nameArray = preg_split('/([[:upper:]][[:lower:]]+)/', $functionName, null, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY)
        $functionName = $nameArray[0] . 'Image' . array_slice($nameArray, 1);
        return call_user_func_array(array($this->getCatalog(), $functionName), $arguments);
    }
}
