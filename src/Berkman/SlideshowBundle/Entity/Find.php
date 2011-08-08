<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Berkman\SlideshowBundle\Entity\Finder
 *
 * Note: The results returned and stored by this class can be of two types - images, or image collections
 */
class Find
{

	const RESULTS_PER_PAGE = 25;

    /**
     * @var string $keyword The keyword for which the user is searching 
     */
    private $keyword;

    /**
     * @var array $repos An array of the repositories under considering in the current search
     */
    private $repos;

	/**
	 * @var array An array that keeps track of various repo positions
     *
     * This is of the form array('repo-id' => array('startIndex' => 0, 'endIndex' => 24), ...)
	 */
	private $repoIndexes;

    /**
     * @var array $selectedResults The results that the user has selected to add to a slideshow
     */
    private $selectedResults;

	/**
	 * @var int $currentPage The number of the current page
	 */
	private $currentPage;

    /**
     * @var array $currentResults An array of the results currently being considered by the user
     */
    private $currentResults;

	/**
	 * @var int $totalResults The number of total results of the search
	 */
	private $totalResults;

    /**
     * Create the finder object 
     *
     * @param array of Berkman\SlideshowBundle\Entity\Repo 
     */
	public function __construct($repos = null)
	{
		if ($repos) {
			$this->repos = $repos;
            foreach ($repos as $repo) {
                $this->repoIndexes[$repo->getId()] = array('startIndex' => 0, 'endIndex' => 0);
            }
		}
		$this->currentPage = 1;
	}

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
     * Get total number of results
     *
     * @return int $numResults
     */
    public function getTotalResults()
    {
        return $this->totalResults;
    }

    /**
     * Get images given a keyword and page
     *
     * @return array $results
     */
	public function findResults($keyword = null, $page = null)
	{
		if (($keyword == null || $keyword == $this->keyword) && $page == $this->currentPage) {
			return $this->currentResults;
		}
		if (empty($keyword) && !empty($this->keyword)) {
			$keyword = $this->keyword;
		}
		elseif (empty($this->keyword) && !empty($keyword)) {
			$this->keyword = $keyword;
		}
		else {
			throw new \ErrorException('No keyword set for search');
		}
		$results = array();
		$totalResults = 0;
		$resultsPerRepo = floor(self::RESULTS_PER_PAGE / count($this->repos));
		$reposFirstIndex = $page * $resultsPerRepo - $resultsPerRepo;
		$reposLastIndex = $reposFirstIndex + $resultsPerRepo - 1;
		$lastRepoLastIndex = $reposLastIndex + self::RESULTS_PER_PAGE % ($resultsPerRepo * count($this->repos));

		foreach ($this->repos as $repo) {
			if ($repo == end($this->repos)) {
                $this->repoIndexes[$repo->getId()] = array(
                    'startIndex' => $reposFirstIndex,
                    'endIndex' => $lastRepoLastIndex
                );
				$searchResults = $repo->fetchSearchResults($keyword, $reposFirstIndex, $lastRepoLastIndex);
			}
			else {
                $this->repoIndexes[$repo->getId()] = array(
                    'startIndex' => $reposFirstIndex,
                    'endIndex' => $reposLastIndex
                );
				$searchResults = $repo->fetchSearchResults($keyword, $reposFirstIndex, $reposLastIndex);
			}
			$results += $searchResults['results'];
			$totalResults += $searchResults['totalResults'];
		}

        $this->currentPage = $page;
		$this->totalResults = $totalResults;
		$this->currentResults = $results;

		return $results;
	}

    /**
     * Get images given a keyword and page
     *
     * @return array $results
     */
	public function findImageCollectionResults($collection, $page = null)
	{
		$images = array();
		$totalResults = 0;
		$firstIndex = $page * self::RESULTS_PER_PAGE - self::RESULTS_PER_PAGE;
		$lastIndex = $firstIndex + self::RESULTS_PER_PAGE - 1;

		if (count($collection->getImages()) > 1) {
			$images = array_slice($collection->getImages(), $firstIndex, $lastIndex - $firstIndex + 1);
			$totalResults = count($collection->getImages());
		}
		else {
			$searchResults = $collection->getRepo()->getFetcher()->fetchImageCollectionResults($collection, $firstIndex, $lastIndex);
			$images = $searchResults['results'];
			$totalResults = $searchResults['totalResults'];
		}

		$this->totalResults = $totalResults;
		$this->images = $images;

		return array('results' => $images, 'totalResults' => $totalResults);
	}
}

