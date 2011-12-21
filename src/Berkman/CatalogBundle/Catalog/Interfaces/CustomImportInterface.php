<?php
namespace Berkman\CatalogBundle\Catalog\Interfaces;

interface CustomImportInterface {
    public function getImagesFromImport(\SplFileObject $file);
    public function getImportInstructions();
}
