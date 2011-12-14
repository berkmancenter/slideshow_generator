<?php
namespace Berkman\CatalogBundle\Catalog;

class CatalogManager {

    private $catalogs;

    public function __construct(array $catalogSlugs)
    {
        foreach ($catalogSlugs as $catalogSlug) {
            $className = '\\Berkman\\CatalogBundle\\Catalog\\Instances\\' . $catalogSlug;
            $this->addCatalog(new $className());
        }
    }

    public function addCatalog($catalog)
    {
        $this->catalogs[] = $catalog;
    }

    public function getCatalogs()
    {
        return $this->catalogs;
    }

    public function getCatalog($id)
    {
        foreach ($this->catalogs as $catalog) {
            if ($catalog->getId() == $id) {
                return $catalog;
            }
        }
    }
}
