<?php
namespace Berkman\CatalogBundle\Interfaces;

interface CustomImportInterface {
    public function getImagesFromImport($file);
    public function getImportInstructions();
}
