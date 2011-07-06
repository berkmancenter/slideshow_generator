<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * Berkman\SlideshowBundle\Entity\Person
 */
class Person extends BaseUser
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var Berkman\SlideshowBundle\Entity\Slideshow
     */
    private $slideshows;

    public function __construct()
    {
		parent::__construct();
        $this->slideshows = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}