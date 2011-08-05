<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Berkman\SlideshowBundle\Fetcher as Fetcher;

/**
 * Berkman\SlideshowBundle\Entity\Repo
 */
class Repo
{
    /**
     * @var string $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

	/**
	 * @var Berkman\SlideshowBundle\Fetcher $fetcher
	 */
	public $fetcher;

    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
		$this->fetcher = $this->getFetcher(); 
    }

    /**
     * Get id
     *
     * @return string $id
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
	 * Get the fetcher object associated with this repo
	 *
	 */
	public function getFetcher()
	{
		$fetcher = null;

		if ($this->fetcher) {
			$fetcher = $this->fetcher;
		}
		else {
			//TODO: figure out a better way to do this stuff
			$className = '\\Berkman\\SlideshowBundle\\Fetcher\\'.$this->getId().'Fetcher';
			if (class_exists($className)) {
				$fetcher = new $className($this);
			}
			else {
				#throw some exception
			}
		}
		return $fetcher;
	}

	/**
	 * Get search results from this repo
	 *
	 * @param string $keyword
	 * @param int $startIndex
	 * @param int $endIndex
	 */
	public function fetchSearchResults($keyword, $startIndex, $endIndex)
	{
		return $this->getFetcher()->fetchSearchResults($keyword, $startIndex, $endIndex);
	}
}
