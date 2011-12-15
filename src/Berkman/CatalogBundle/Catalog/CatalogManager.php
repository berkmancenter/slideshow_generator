<?php
namespace Berkman\CatalogBundle\Catalog;

class CatalogManager {

    private $catalogs = array();

    public function __construct(array $catalogIds = array())
    {
        foreach ($catalogIds as $catalogId) {
            $this->addCatalogById($catalogId);
        }
    }

    public function addCatalog($catalog)
    {
        $this->catalogs[] = $catalog;
    }

    public function addCatalogById($catalogId)
    {
        $className = '\\Berkman\\CatalogBundle\\Catalog\\Instances\\' . $catalogId;
        $this->addCatalog(new $className());
    }

    public function getCatalogs()
    {
        return $this->catalogs;
    }

    public function getCatalog($id)
    {
        $selectedCatalog = null;
        foreach ($this->catalogs as $catalog) {
            if ($catalog->getId() == $id) {
                $selectedCatalog = $catalog;
            }
        }
        if ($selectedCatalog === null) {
            $this->addCatalogById($id);
            $selectedCatalog = $this->getCatalog($id);
        }
        return $selectedCatalog;
    }
}
