<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Berkman\SlideshowBundle\Entity\Slide
 */
class Slide
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $position
     */
    private $position;

    /**
     * @var Berkman\SlideshowBundle\Entity\Image
     */
    private $image;

    /**
     * @var Berkman\SlideshowBundle\Entity\Slideshow
     */
    private $slideshow;


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
     * Set position
     *
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get position
     *
     * @return integer $position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set image
     *
     * @param Berkman\SlideshowBundle\Entity\Image $image
     */
    public function setImage(\Berkman\SlideshowBundle\Entity\Image $image)
    {
        $this->image = $image;
    }

    /**
     * Get image
     *
     * @return Berkman\SlideshowBundle\Entity\Image $image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set slideshow
     *
     * @param Berkman\SlideshowBundle\Entity\Slideshow $slideshow
     */
    public function setSlideshow(\Berkman\SlideshowBundle\Entity\Slideshow $slideshow)
    {
        $this->slideshow = $slideshow;
		$this->position = count($this->getSlideshow()->getSlides()) + 1;
    }

    /**
     * Get slideshow
     *
     * @return Berkman\SlideshowBundle\Entity\Slideshow $slideshow
     */
    public function getSlideshow()
    {
        return $this->slideshow;
    }

	/**
	 * Create a slide from an Image
	 *
	 * @param Berkman\SlideshowBundle\Entity\Image $image
	 */
	public function __construct(Image $image)
	{
		$this->setImage($image);
	}

	/**
	 * Use a thumnail URL as the __toString return
	 *
	 * @return string $thumbnailUrl
	 */
	public function __toString()
	{
		return $this->getImage()->getThumbnailUrl();
	}
}