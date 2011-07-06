<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Berkman\SlideshowBundle\Entity\Find
 */
class Find
{
    /**
     * @var string $keyword
     */
    private $keyword;

    /**
     * @var array $repos
     */
    private $repos;

	/**
	 * This is an array of format:
	 * array(repoId => array('currentPage' => int, 'numResults' => int), repoId => ...)
	 *
	 * @var array $reposInfo
	 */
	private $reposInfo;

    /**
     * @var array $results
     */
    private $results;

	/**
	 * @var int $currentPage
	 */
	private $currentPage;

	/**
	 * @var int $numResults
	 */
	private $numResults;

    /**
     * Set keyword
     *
     * @param string $keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    }

    /**
     * Get keyword
     *
     * @return string $keyword
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set info about the repos
     *
     * @param array $reposInfo
     */
    public function setReposInfo($reposInfo)
    {
        $this->reposInfo = $reposInfo;
    }

    /**
     * Get info about the repos
     *
     * @return array $reposInfo
     */
    public function getReposInfo()
    {
        return $this->reposInfo;
    }

    /**
     * Get total number of results
     *
     * @return int $numResults
     */
    public function getNumResults()
    {
        return $this->numResults;
    }

    /**
     * Set repos
     *
     * @param array $repos
     */
    public function setRepos($repos)
    {
		$this->repos = $repos;
    }

    /**
     * Get repos
     *
     * @return array $repos
     */
    public function getRepos()
    {
        return $this->repos;
    }

    /**
     * Set results
     *
     * @param array $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * Get results as an array of images
     *
     * @return array $results
     */
	public function getResults($keyword = null, $page = null)
	{
		$images = array();
		$numResults = 0;

		if ($page == null) {
			$page = $this->currentPage;
		}

		if ($keyword == null) {
			$keyword = $this->getKeyword();
		}

		if ($keyword == null && $page == $this->currentPage) {
			$images = $this->results;
		}
		else {
			foreach ($this->repos as $repo) {
				//TODO: Setup some kind of real pagination
				//$repoPage = ($page == $this->currentPage) ? $repoInfo['currentPage'] : $repoInfo['currentPage'] + 1;
				$searchUrl = str_replace(
					array('{keyword}', '{page}'),
					array($keyword, $page), $repo->getSearchUrlPattern()
				);
				$curl = curl_init($searchUrl);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
				#curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
				$response = curl_exec($curl);
				$parser = $repo->getParser();
				$parser->setInput($response);
				$images += $parser->getImages();
				$numResults += $parser->getNumResults();
			}
		}

		$this->numResults = $numResults;
		$this->results = $images;

		return $images;
	}

	public function __construct($keyword = null, $repos = null)
	{
		if ($keyword) {
			$this->keyword = $keyword;
		}
		if ($repos) {
			$this->setRepos($repos);
		}
		$this->currentPage = 1;
	}
}

