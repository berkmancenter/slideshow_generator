<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Berkman\SlideshowBundle\Entity\Slideshow
 */
class Slideshow
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
     * @var smallint $slide_delay
     */
    private $slide_delay;

    /**
     * @var boolean $display_info
     */
    private $display_info;


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
     * Set slide_delay
     *
     * @param smallint $slideDelay
     */
    public function setSlideDelay($slideDelay)
    {
        $this->slide_delay = $slideDelay;
    }

    /**
     * Get slide_delay
     *
     * @return smallint $slideDelay
     */
    public function getSlideDelay()
    {
        return $this->slide_delay;
    }

    /**
     * Set display_info
     *
     * @param boolean $displayInfo
     */
    public function setDisplayInfo($displayInfo)
    {
        $this->display_info = $displayInfo;
    }

    /**
     * Get display_info
     *
     * @return boolean $displayInfo
     */
    public function getDisplayInfo()
    {
        return $this->display_info;
    }
    /**
     * @var Berkman\SlideshowBundle\Entity\User
     */
    private $user;


    /**
     * Set user
     *
     * @param Berkman\SlideshowBundle\Entity\User $user
     */
    public function setUser(\Berkman\SlideshowBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return Berkman\SlideshowBundle\Entity\User $user
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var Berkman\SlideshowBundle\Entity\Person
     */
    private $person;


    /**
     * Set person
     *
     * @param Berkman\SlideshowBundle\Entity\Person $person
     */
    public function setPerson(\Berkman\SlideshowBundle\Entity\Person $person)
    {
        $this->person = $person;
    }

    /**
     * Get person
     *
     * @return Berkman\SlideshowBundle\Entity\Person $person
     */
    public function getPerson()
    {
        return $this->person;
    }
}