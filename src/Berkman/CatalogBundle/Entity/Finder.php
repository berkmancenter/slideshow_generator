<?php
namespace Berkman\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Berkman\CatalogBundle\Entity\Catalog;

/**
 * Berkman\SlideshowBundle\Entity\Finder
 *
 * Notes:
 *   This has an image collection and an image group collection.
 *   Every image and imageGroup is assigned an ID based on the count
 *   Current and selected images and image groups are just arrays of the IDs
 */
class Finder
{
    const RESULTS_PER_PAGE = 25;

    /**
     * @var string $keyword
     */
    private $keyword;

    /**
     * @var array $history_stack
     */
    private $history_stack;

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
     * @var array $imageGroups
     */
    private $imageGroups;

    /**
     * @var Berkman\SlideshowBundle\Entity\Catalog
     */
    private $catalogs;

    /**
     * @var Berkman\SlideshowBundle\Entity\Image
     */
    private $current_image_results;

    /**
     * @var Berkman\SlideshowBundle\Entity\ImageGroup
     */
    private $current_imageGroup_results;

    /**
     * @var Berkman\SlideshowBundle\Entity\Image
     */
    private $selected_image_results;

    public function __construct($catalogManager = null)
    {
        $this->images                     = array();
        $this->imageGroups                = array();
        $this->current_image_results      = array();
        $this->current_imageGroup_results = array();
        $this->selected_image_results     = array();
        $this->history_stack              = array();
        $this->results_per_page           = self::RESULTS_PER_PAGE;
        $this->current_page               = 1;
        if (isset($catalogManager)) {
            $this->catalogs               = $catalogManager->getCatalogs();
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
     * Add catalogs
     *
     * @param Berkman\SlideshowBundle\Entity\Catalog $catalogs
     */
    public function addCatalogs(Catalog $catalogs)
    {
        $this->catalogs[] = $catalogs;
    }

    /**
     * Set catalogs
     *
     * @param array $catalogs
     */
    public function setCatalogs($catalogs)
    {
        if ($catalogs instanceof Catalog) {
            $catalogs = array($catalogs);
        }
        $this->catalogs = $catalogs;
    }

    /**
     * Get catalogs
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCatalogs()
    {
        return $this->catalogs;
    }

    public function getCatalog($id)
    {
        foreach($this->catalogs as $catalog) {
            if ($catalog->getId() == $id) {
                return $catalog;
            }
        }
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
     * Get imageGroup by id
     *
     * @param integer $id
     * @return Berkman\SlideshowBundle\Entity\ImageGroup
     */
    public function getImageGroup($id)
    {
        foreach($this->getImageGroups() as $imageGroup) {
            if ($imageGroup->getId() == $id)
                return $imageGroup;
        }
    }

    /**
     * Add imageGroup
     *
     * @param Berkman\SlideshowBundle\Entity\ImageGroup
     * @return integer $imageGroupId
     */
    public function addImageGroup(ImageGroup $imageGroup)
    {
        $imageGroupId = $imageGroup->getId();
        if (empty($imageGroupId)) {
            $imageGroup->setId(count($this->imageGroups));
        }
        $this->imageGroups[] = $imageGroup;
        return $imageGroup->getId();
    }

    /**
     * Set imageGroups
     *
     * @param array $imageGroups
     */
    public function setImageGroups($imageGroups)
    {
        $this->imageGroups = $imageGroups;
    }

    /**
     * Get images
     *
     * @return array $imageGroups
     */
    public function getImageGroups()
    {
        return $this->imageGroups;
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
     * Get current_imageGroup_results
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCurrentImageGroupResults()
    {
        $imageGroupResults = array();
        foreach ($this->current_imageGroup_results as $imageGroupId) {
            $imageGroupResults[] = $this->getImageGroup($imageGroupId);
        }
        return $imageGroupResults;
    }

    /**
     * Add current_imageGroup_results
     *
     * @param integer $imageGroupId
     */
    public function addCurrentImageGroupResult($imageGroupId)
    {
        $this->current_imageGroup_results[] = $imageGroupId;
    }

    /**
     * Set current_imageGroup_results
     *
     * @param integer $currentImageGroupResults
     */
    public function setCurrentImageGroupResults($currentImageGroupResults)
    {
        $this->current_imageGroup_results = $currentImageGroupResults;
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

    public function getHistoryStack()
    {
        return $this->history_stack;
    }

    public function setHistoryStack(array $stack)
    {
        $this->history_stack = $stack;
    }

    public function pushHistoryStack($uri)
    {
        $this->history_stack[] = $uri;
    }

    public function popHistoryStack()
    {
        return array_pop($this->history_stack);
    }

    /**
     * Get images given a keyword and page
     *
     * @return array $results
     */
    public function findResults($keyword = null, $page = 1)
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
        $imageGroupResults = array();
        $totalResults      = 0;
        $numOfCatalogs        = count($this->catalogs);
        $resultsPerCatalog    = floor($this->getResultsPerPage() / $numOfCatalogs);
        $catalogsFirstIndex   = $resultsPerCatalog * ($page - 1);

        foreach ($this->catalogs as $catalog) {
            $searchResults = $catalog->fetchResults($keyword, $catalogsFirstIndex, $resultsPerCatalog);
            array_splice($results, count($results), 0, $searchResults['results']);
            $totalResults += $searchResults['totalResults'];
        }

        foreach ($results as $result) {
            if ($result instanceof Image) {
                $imageResults[] = $this->addImage($result);
            }
            elseif ($result instanceof ImageGroup) {
                $imageGroupResults[] = $this->addImageGroup($result);
            }
        }
        $this->setCurrentImageResults($imageResults);
        $this->setCurrentImageGroupResults($imageGroupResults);

        $this->setCurrentPage($page);
        $this->setTotalPages(ceil($totalResults / $this->getResultsPerPage()));
        $this->setTotalResults($totalResults);

        return array('results' => $results, 'totalResults' => $totalResults);
    }

    /**
     * Get results given a imageGroup and page
     *
     * @return array $results
     */
    public function findImageGroupResults($imageGroup, $page = 1)
    {
        $results           = array();
        $imageResults      = array();
        $imageGroupResults = array();
        $totalResults      = 0;
        $firstIndex        = $this->getResultsPerPage() * ($page - 1);

        $searchResults = $imageGroup->getCatalog()->fetchImageGroupResults($imageGroup, $firstIndex, $this->getResultsPerPage());
        $results = $searchResults['results'];
        $totalResults = $searchResults['totalResults'];

        foreach ($results as $result) {
            if ($result instanceof Image) {
                $imageResults[] = $this->addImage($result);
            }
            elseif ($result instanceof ImageGroup) {
                $imageGroupResults[] = $this->addImageGroup($result);
            }
        }
        $this->setCurrentPage($page);
        $this->setCurrentImageResults($imageResults);
        $this->setCurrentImageGroupResults($imageGroupResults);
        $this->setTotalPages(ceil($totalResults / $this->getResultsPerPage()));
        $this->setTotalResults($totalResults);

        return array('results' => $results, 'totalResults' => $totalResults);
    }
}
