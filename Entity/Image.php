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
}