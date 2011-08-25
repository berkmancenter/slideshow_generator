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
     * @var integer $total_pages
     */
    private $total_pages;

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

    public function __construct($repos = null)
    {
        $this->repos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->current_image_results = new \Doctrine\Common\Collections\ArrayCollection();
        $this->current_collection_results = new \Doctrine\Common\Collections\ArrayCollection();
        $this->selected_image_results = new \Doctrine\Common\Collections\ArrayCollection();
        $this->selected_colletion_results = new \Doctrine\Common\Collections\ArrayCollection();
		if ($repos) {
			$this->repos = $repos;
            $repoIndxes = $this->getRepoIndexes();
            foreach ($repos as $repo) {
                $repoIndexes[$repo->getId()] = array('startIndex' => 0, 'endIndex' => 0);
            }
            $this->setRepoIndexes($repoIndexes);
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
     * Set total_pages
     *
     * @param integer 
     */
    public function setTotalPages($totalPages)
    {
        $this->total_pages = $totalPages;
    }
    /**
     * Get total_pages
     *
     * @return integer 
     */
    public function getTotalPages()
    {
        return $this->total_pages;
    }

    /**
     * Set repo_indexes
     *
     * @param string $repoIndexes
     */
    public function setRepoIndexes($repoIndexes)
    {
        $this->repo_indexes = serialize($repoIndexes);
    }

    /**
     * Get repo_indexes
     *
     * @return string 
     */
    public function getRepoIndexes()
    {
        return unserialize($this->repo_indexes);
    }

    /**
     * Add repos
     *
     * @param Berkman\SlideshowBundle\Entity\Repo $repos
     */
    public function addRepos(\Berkman\SlideshowBundle\Entity\Repo $repos)
    {
        $this->repos[] = $repos;
        $repoIndexes = $this->getRepoIndexes();
        $repoIndexes[$repos->getId()] = array('startIndex' => 0, 'endIndex' => 0);
        $this->setRepoIndexes($repoIndexes);
    }

    /**
     * Set repos
     *
     * @param array $repos
     */
    public function setRepos($repos)
    {
        $this->repos = new \Doctrine\Common\Collections\ArrayCollection();
        $repoIndexes = array();
        foreach ($repos as $repo) {
            $this->repos[] = $repo;
            $repoIndexes[$repo->getId()] = array('startIndex' => 0, 'endIndex' => 0);
        }
        $this->setRepoIndexes($repoIndexes);
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
     * Get current_image_results
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCurrentImageResult($id)
    {
        foreach ($this->getCurrentImageResults() as $image) {
            if ($image->getId() == $id) {
                return $image;
            }
        }
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
     * Get current_collection_result
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCurrentCollectionResult($id)
    {
        foreach ($this->getCurrentCollectionResults() as $collection) {
            if ($collection->getId() == $id) {
                return $collection;
            }
        }
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
     * Set selected_image_results
     *
     * @param array $selectedImageResults
     */
    public function setSelectedImageResults($selectedImageResults)
    {
        $this->selected_image_results = $selectedImageResults;
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
     * Get images given a keyword and page
     *
     * @return array $results
     */
	public function findResults($keyword = null, $page = null)
	{
		/*if (($keyword == null || $keyword == $this->keyword) && $page == $this->current_page) {
            return array(
                'results' => $this->getCurrentImageResults() + $this->getCurrentCollectionResults(),
                'totalResults' => $this->getTotalResults()
            );
        }*/
		if (empty($keyword) && !empty($this->keyword)) {
			$keyword = $this->keyword;
		}
		elseif (!empty($keyword)) {
			$this->keyword = $keyword;
		}
		else {
			throw new \ErrorException('No keyword set for search');
		}
		$results           = array();
        $imageResults =  new \Doctrine\Common\Collections\ArrayCollection();
        $collectionResults =  new \Doctrine\Common\Collections\ArrayCollection();
		$totalResults      = 0;
		$resultsPerRepo    = floor($this->getResultsPerPage() / count($this->repos));
		$reposFirstIndex   = $page * $resultsPerRepo - $resultsPerRepo;
		$reposLastIndex    = $reposFirstIndex + $resultsPerRepo - 1;
		$lastRepoLastIndex = $reposLastIndex + $this->getResultsPerPage() % ($resultsPerRepo * count($this->repos));
        $repoIndexes       = $this->getRepoIndexes();

		foreach ($this->repos as $repo) {
			if ($repo == end($this->repos)) {
                $reposLastIndex = $lastRepoLastIndex;
			}
            $searchResults = $repo->fetchResults($keyword, $reposFirstIndex, $reposLastIndex);
            array_splice($results, count($results), 0, $searchResults['results']);
            $repoIndexes[$repo->getId()] = array(
                'startIndex' => $reposFirstIndex,
                'endIndex' => $searchResults['endIndex']
            );
			$totalResults += $searchResults['totalResults'];
		}

        foreach ($results as $result) {
            if ($result instanceof Image) {
                $imageResults[] = $result;
            }
            elseif ($result instanceof Collection) {
                $collectionResults[] = $result;
            }
        }
		$this->current_image_results = $imageResults;
		$this->current_collection_results = $collectionResults;

        $this->setRepoIndexes($repoIndexes);
        $this->setCurrentPage($page);
		$this->setTotalResults($totalResults);
        $this->setTotalPages(floor($totalResults / $this->getResultsPerPage()));

		return array('results' => $results, 'totalResults' => $totalResults);
	}

    /**
     * Get results given a collection and page
     *
     * @return array $results
     */
	public function findCollectionResults($collection, $page = null)
	{
		$results = array();
        $imageResults =  new \Doctrine\Common\Collections\ArrayCollection();
        $collectionResults =  new \Doctrine\Common\Collections\ArrayCollection();
		$totalResults = 0;
		$firstIndex = $page * $this->getResultsPerPage() - $this->getResultsPerPage();
		$lastIndex = $firstIndex + $this->getResultsPerPage() - 1;

		if (count($collection->getImages()) > 1) {
			$results = array_slice($collection->getImages()->toArray(), $firstIndex, $lastIndex - $firstIndex);
			$totalResults = count($collection->getImages());
		}
		else {
			$searchResults = $collection->getRepo()->getFetcher()->fetchCollectionResults($collection, $firstIndex, $lastIndex);
			$results = $searchResults['results'];
			$totalResults = $searchResults['totalResults'];
		}

        foreach ($results as $result) {
            if ($result instanceof Image) {
                $imageResults[] = $result;
            }
            elseif ($result instanceof Collection) {
                $collectionResults[] = $result;
            }
        }
		$this->current_image_results = $imageResults;
		$this->current_collection_results = $collectionResults;

		$this->setTotalResults($totalResults);

		return array('results' => $results, 'totalResults' => $totalResults);
	}
}
