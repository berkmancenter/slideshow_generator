<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Berkman\SlideshowBundle\Fetcher as Fetcher;

/**
 * Berkman\SlideshowBundle\Entity\Catalog
 */
class Catalog
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
        return $this->name . ' (' . $this->getId() . ')';
    }

	/**
	 * Get the fetcher object associated with this catalog
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
	 * Get search results from this catalog
	 *
	 * @param string $keyword
	 * @param int $startIndex
	 * @param int $endIndex
	 */
	public function fetchResults($keyword, $startIndex, $count)
	{
		return $this->getFetcher()->fetchResults($keyword, $startIndex, $count);
	}
    /**
     * @var datetime $created
     */
    private $created;

    /**
     * @var datetime $updated
     */
    private $updated;


    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param datetime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * Get updated
     *
     * @return datetime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    public function getImportFormat()
    {
        return $this->getFetcher()->getImportFormat();
    }

    public function hasCustomImporter()
    {
        return $this->getFetcher()->hasCustomImporter();
    }
}
