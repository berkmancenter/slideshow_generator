<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Berkman\SlideshowBundle\Entity\Finder
 */
class Finder
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $results_per_page
     */
    private $results_per_page;

    /**
     * @var string $keyword
     */
    private $keyword;

    /**
     * @var integer $current_page
     */
    private $current_page;

    /**
     * @var integer $total_results
     */
    private $total_results;

    /**
     * @var string $repo_indexes
     */
    private $repo_indexes;

    /**
     * @var Berkman\SlideshowBundle\Entity\Repo
     */
    private $repos;

    /**
     * @var Berkman\SlideshowBundle\Entity\Image
     */
    private $current_image_results;

    /**
     * @var Berkman\SlideshowBundle\Entity\Collection
     */
    private $current_collection_results;

    /**
     * @var Berkman\SlideshowBundle\Entity\Image
     */
    private $selected_image_results;

    /**
     * @var Berkman\SlideshowBundle\Entity\Collection
     */
    private $selected_colletion_results;

    public function __construct($repos = null)
    {
        $this->repos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->current_image_results = new \Doctrine\Common\Collections\ArrayCollection();
        $this->current_collection_results = new \Doctrine\Common\Collections\ArrayCollection();
        $this->selected_image_results = new \Doctrine\Common\Collections\ArrayCollection();
        $this->selected_colletion_results = new \Doctrine\Common\Collections\ArrayCollection();
		if ($repos) {
			$this->repos = $repos;
            foreach ($repos as $repo) {
                $this->repoIndexes[$repo->getId()] = array('startIndex' => 0, 'endIndex' => 0);
            }
		}
		$this->setCurrentPage(1);
        $this->setResultsPerPage(25);
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

    /**
     * Set results_per_page
     *
     * @param integer $resultsPerPage
     */
    public function setResultsPerPage($resultsPerPage)
    {
        $this->results_per_page = $resultsPerPage;
    }

    /**
     * Get results_per_page
     *
     * @return integer 
     */
    public function getResultsPerPage()
    {
        return $this->results_per_page;
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
     * @return string 
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set current_page
     *
     * @param integer $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->current_page = $currentPage;
    }

    /**
     * Get current_page
     *
     * @return integer 
     */
    public function getCurrentPage()
    {
        return $this->current_page;
    }

    /**
     * Set total_results
     *
     * @param integer $totalResults
     */
    public function setTotalResults($totalResults)
    {
        $this->total_results = $totalResults;
    }

    /**
     * Get total_results
     *
     * @return integer 
     */
    public function getTotalResults()
    {
        return $this->total_results;
    }

    /**
     * Set repo_indexes
     *
     * @param string $repoIndexes
     */
    public function setRepoIndexes($repoIndexes)
    {
        $this->repo_indexes = $repoIndexes;
    }

    /**
     * Get repo_indexes
     *
     * @return string 
     */
    public function getRepoIndexes()
    {
        return $this->repo_indexes;
    }

    /**
     * Add repos
     *
     * @param Berkman\SlideshowBundle\Entity\Repo $repos
     */
    public function addRepos(\Berkman\SlideshowBundle\Entity\Repo $repos)
    {
        $this->repos[] = $repos;
    }

    /**
     * Get repos
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRepos()
    {
        return $this->repos;
    }

    /**
     * Add current_image_results
     *
     * @param Berkman\SlideshowBundle\Entity\Image $currentImageResults
     */
    public function addCurrentImageResults(\Berkman\SlideshowBundle\Entity\Image $currentImageResults)
    {
        $this->current_image_results[] = $currentImageResults;
    }

    /**
     * Get current_image_results
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCurrentImageResults()
    {
        return $this->current_image_results;
    }

    /**
     * Add current_collection_results
     *
     * @param Berkman\SlideshowBundle\Entity\Collection $currentCollectionResults
     */
    public function addCurrentCollectionResults(\Berkman\SlideshowBundle\Entity\Collection $currentCollectionResults)
    {
        $this->current_collection_results[] = $currentCollectionResults;
    }

    /**
     * Get current_collection_results
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCurrentCollectionResults()
    {
        return $this->current_collection_results;
    }

    /**
     * Add selected_image_results
     *
     * @param Berkman\SlideshowBundle\Entity\Image $selectedImageResults
     */
    public function addSelectedImageResults(\Berkman\SlideshowBundle\Entity\Image $selectedImageResults)
    {
        $this->selected_image_results[] = $selectedImageResults;
    }

    /**
     * Get selected_image_results
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSelectedImageResults()
    {
        return $this->selected_image_results;
    }

    /**
     * Add selected_colletion_results
     *
     * @param Berkman\SlideshowBundle\Entity\Collection $selectedColletionResults
     */
    public function addSelectedColletionResults(\Berkman\SlideshowBundle\Entity\Collection $selectedColletionResults)
    {
        $this->selected_colletion_results[] = $selectedColletionResults;
    }

    /**
     * Get selected_colletion_results
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSelectedColletionResults()
    {
        return $this->selected_colletion_results;
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
		$resultsPerRepo = floor($this->getResultsPerPage() / count($this->repos));
		$reposFirstIndex = $page * $resultsPerRepo - $resultsPerRepo;
		$reposLastIndex = $reposFirstIndex + $resultsPerRepo - 1;
		$lastRepoLastIndex = $reposLastIndex + $this->getResultsPerPage() % ($resultsPerRepo * count($this->repos));

		foreach ($this->repos as $repo) {
			if ($repo == end($this->repos)) {
                $this->repoIndexes[$repo->getId()] = array(
                    'startIndex' => $reposFirstIndex,
                    'endIndex' => $lastRepoLastIndex
                );
				$searchResults = $repo->fetchResults($keyword, $reposFirstIndex, $lastRepoLastIndex);
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

		return array('results' => $results, 'totalResults' => $totalResults);
	}

    /**
     * Get results given a collection and page
     *
     * @return array $results
     */
	public function findCollectionResults($collection, $page = null)
	{
		$images = array();
		$totalResults = 0;
		$firstIndex = $page * $this->getResultsPerPage() - $this->getResultsPerPage();
		$lastIndex = $firstIndex + $this->getResultsPerPage() - 1;

		if (count($collection->getImages()) > 1) {
			$images = array_slice($collection->getImages(), $firstIndex, $lastIndex - $firstIndex + 1);
			$totalResults = count($collection->getImages());
		}
		else {
			$searchResults = $collection->getRepo()->getFetcher()->fetchCollectionResults($collection, $firstIndex, $lastIndex);
			$images = $searchResults['results'];
			$totalResults = $searchResults['totalResults'];
		}

		$this->totalResults = $totalResults;
		$this->images = $images;

		return array('results' => $images, 'totalResults' => $totalResults);
	}
}