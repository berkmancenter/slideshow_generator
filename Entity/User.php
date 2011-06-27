<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Berkman\SlideshowBundle\Entity\User
 */
class User
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $username
     */
    private $username;

    /**
     * @var Berkman\SlideshowBundle\Entity\Slideshow
     */
    private $slideshows;

    public function __construct()
    {
        $this->slideshows = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string $username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Add slideshows
     *
     * @param Berkman\SlideshowBundle\Entity\Slideshow $slideshows
     */
    public function addSlideshows(\Berkman\SlideshowBundle\Entity\Slideshow $slideshows)
    {
        $this->slideshows[] = $slideshows;
    }

    /**
     * Get slideshows
     *
     * @return Doctrine\Common\Collections\Collection $slideshows
     */
    public function getSlideshows()
    {
        return $this->slideshows;
    }
    /**
     * @var string $password
     */
    private $password;


    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }
}