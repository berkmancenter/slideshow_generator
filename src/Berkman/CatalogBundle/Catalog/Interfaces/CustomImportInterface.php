<?php
namespace Berkman\CatalogBundle\Catalog\Interfaces;

interface CustomImportInterface {
    public function getImagesFromImport($file);
    public function getImportInstructions();
}
