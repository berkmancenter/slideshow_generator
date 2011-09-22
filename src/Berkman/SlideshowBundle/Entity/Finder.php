<?php

namespace Berkman\SlideshowBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Berkman\SlideshowBundle\Entity\Finder
 */
class Finder
{
    const RESULTS_PER_PAGE = 25;

    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $keyword
     */
    private $keyword;

    /**
     * @var integer $current_page
     */
    private $current_page;

    /**
     * @var integer $results_per_page
     */
    private $results_per_page;

    /**
     * @var integer $total_results
     */
    private $total_results;

    /**
     * @var integer $total_pages
     */
    private $total_pages;

    /**
     * @var array $images
     */
    private $images;

    /**
     * @var array $collections
     */
    private $collections;

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
        $this->images                     = array();
        $this->collections                = array();
        $this->current_image_results      = array();
        $this->current_collection_results = array();
        $this->selected_image_results     = array();
        $this->results_per_page           = self::RESULTS_PER_PAGE;
		$this->current_page               = 1;
		if (isset($repos)) {
			$this->repos                  = $repos;
		}
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
     * Set results_per_page
     *
     * @param integer $results_per_page
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
        if ($this->results_per_page < 1) {
            return self::RESULTS_PER_PAGE;
        }

        return $this->results_per_page;
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
     * Add repos
     *
     * @param Berkman\SlideshowBundle\Entity\Repo $repos
     */
    public function addRepos(\Berkman\SlideshowBundle\Entity\Repo $repos)
    {
        $this->repos[] = $repos;
    }

    /**
     * Set repos
     *
     * @param array $repos
     */
    public function setRepos($repos)
    {
        if ($repos instanceof Repo) {
            $repos = array($repos);
        }
        $this->repos = $repos;
    }

    /**
     * Get repos
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRepos()
    {
        return new ArrayCollection($this->repos);
    }

    /**
     * Get image by id
     *
     * @param integer $id
     * @return Berkman\SlideshowBundle\Entity\Image
     */
    public function getImage($id)
    {
        foreach($this->getImages() as $image) {
            if ($image->getId() == $id)
                return $image;
        }
    }

    /**
     * Add image
     *
     * @param Berkman\SlideshowBundle\Entity\Image
     * @return integer $imageId
     */
    public function addImage(Image $image)
    {
        $imageId = $image->getId();
        if (empty($imageId)) {
            $image->setId(count($this->images));
        }
        $this->images[] = $image;
        return $image->getId();
    }

    /**
     * Set images
     *
     * @param array $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * Get images
     *
     * @return array $images
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Get collection by id
     *
     * @param integer $id
     * @return Berkman\SlideshowBundle\Entity\Collection
     */
    public function getCollection($id)
    {
        foreach($this->getCollections() as $collection) {
            if ($collection->getId() == $id)
                return $collection;
        }
    }

    /**
     * Add collection
     *
     * @param Berkman\SlideshowBundle\Entity\Collection
     * @return integer $collectionId
     */
    public function addCollection(Collection $collection)
    {
        $collectionId = $collection->getId();
        if (empty($collectionId)) {
            $collection->setId(count($this->collections));
        }
        $this->collections[] = $collection;
        return $collection->getId();
    }

    /**
     * Set collections
     *
     * @param array $collections
     */
    public function setCollections($collections)
    {
        $this->collections = $collections;
    }

    /**
     * Get images
     *
     * @return array $collections
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * Get current_image_results
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCurrentImageResults()
    {
        $imageResults = array();
        foreach ($this->current_image_results as $imageId) {
            $imageResults[] = $this->getImage($imageId);
        }
        return $imageResults;
    }

    /**
     * Add current_image_results
     *
     * @param integer $image_id
     */
    public function addCurrentImageResult($imageId)
    {
        $this->current_image_results[] = $imageId;
    }

    /**
     * Set current_image_results
     *
     * @param array $currentImageResults
     */
    public function setCurrentImageResults($currentImageResults)
    {
        $this->current_image_results = $currentImageResults;
    }

    /**
     * Get current_collection_results
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCurrentCollectionResults()
    {
        $collectionResults = array();
        foreach ($this->current_collection_results as $collectionId) {
            $collectionResults[] = $this->getCollection($collectionId);
        }
        return $collectionResults;
    }

    /**
     * Add current_collection_results
     *
     * @param integer $collectionId
     */
    public function addCurrentCollectionResult($collectionId)
    {
        $this->current_collection_results[] = $collectionId;
    }

    /**
     * Set current_collection_results
     *
     * @param integer $currentCollectionResults
     */
    public function setCurrentCollectionResults($currentCollectionResults)
    {
        $this->current_collection_results = $currentCollectionResults;
    }

    /**
     * Get selected_image_results
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getSelectedImageResults()
    {
        $imageResults = array();
        foreach ($this->selected_image_results as $imageId) {
            $imageResults[] = $this->getImage($imageId);
        }
        return $imageResults;
    }

    /**
     * Add selected_image_results
     *
     * @param integer $imageId
     */
    public function addSelectedImageResult($imageId)
    {
        $this->selected_image_results[] = $imageId;
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
     * Get images given a keyword and page
     *
     * @return array $results
     */
	public function findResults($keyword = null, $page = null)
	{
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
        $imageResults      = array();
        $collectionResults = array();
		$totalResults      = 0;
        $numOfRepos        = count($this->repos);
        $resultsPerRepo    = floor($this->getResultsPerPage() / $numOfRepos);
		$reposFirstIndex   = $resultsPerRepo * ($page - 1);

		foreach ($this->repos as $repo) {
            $searchResults = $repo->fetchResults($keyword, $reposFirstIndex, $resultsPerRepo);
            array_splice($results, count($results), 0, $searchResults['results']);
			$totalResults += $searchResults['totalResults'];
		}

        foreach ($results as $result) {
            if ($result instanceof Image) {
                $imageResults[] = $this->addImage($result);
            }
            elseif ($result instanceof Collection) {
                $collectionResults[] = $this->addCollection($result);
            }
        }
		$this->setCurrentImageResults($imageResults);
		$this->setCurrentCollectionResults($collectionResults);

        $this->setCurrentPage($page);
        $this->setTotalPages(ceil($totalResults / $this->getResultsPerPage()));
		$this->setTotalResults($totalResults);

		return array('results' => $results, 'totalResults' => $totalResults);
	}

    /**
     * Get results given a collection and page
     *
     * @return array $results
     */
	public function findCollectionResults($collection, $page = null)
	{
		$results           = array();
        $imageResults      = array();
        $collectionResults = array();
		$totalResults      = 0;
		$firstIndex        = $page * $this->getResultsPerPage() - $this->getResultsPerPage();
		$lastIndex         = $firstIndex + $this->getResultsPerPage() - 1;

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
                $imageResults[] = $this->addImage($result);
            }
            elseif ($result instanceof Collection) {
                $collectionResults[] = $this->addCollection($result);
            }
        }
		$this->setCurrentImageResults($imageResults);
		$this->setCurrentCollectionResults($collectionResults);
		$this->setTotalResults($totalResults);

		return array('results' => $results, 'totalResults' => $totalResults);
	}
}
