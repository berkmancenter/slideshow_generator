<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
	 * This is an array of format:
	 * array('repo' => Repo, 'currentPage' => int, 'numResults' => int)
	 *
     * @var array $repos
     */
    private $repos;

    /**
     * @var array $results
     */
    private $results;

	/**
	 * @var int $currentPage
	 */
	private $currentPage;

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
     * Set repos
     *
     * @param array $repos
     */
    public function setRepos($repos)
    {
		$findRepos = array();
		foreach ($repos as $repo) {
			$findRepos[] = array('repo' => $repo, 'currentPage' => 1, 'numResults' => 0);
		}

        $this->repos = $findRepos;
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
			foreach ($this->repos as $repoInfo) {
				$repo = $repoInfo['repo'];
				$repoPage = ($page == $this->currentPage) ? $repoInfo['currentPage'] : $repoInfo['currentPage'] + 1;
				$searchUrl = str_replace(array('{keyword}', '{page}'), array($keyword, $repoPage), $repo->getSearchUrlPattern());
				$curl = curl_init($searchUrl);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
				#curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json"));
				$response = curl_exec($curl);
				$parser = $repo->getParser();
				$images += $parser->getImages($response);
			}
		}

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

