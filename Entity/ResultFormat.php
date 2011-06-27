<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Berkman\SlideshowBundle\Entity\ResultFormat
 */
class ResultFormat
{
    /**
     * @var string $code
     */
    private $code;

    /**
     * @var string $name
     */
    private $name;


    /**
     * Set code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code
     *
     * @return string $code
     */
    public function getCode()
    {
        return $this->code;
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
     * @var string $id
     */
    private $id;


    /**
     * Set id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
	 * Get string representation
	 *
	 * @return string $repo_code
	 */
	public function __toString()
	{
		return $this->getName();
	}
}
