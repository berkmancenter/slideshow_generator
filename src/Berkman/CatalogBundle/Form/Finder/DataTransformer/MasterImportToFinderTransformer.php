<?php
namespace Berkman\CatalogBundle\Form\Finder\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\DataTransformerInterface;

use Berkman\CatalogBundle\Entity\Finder;

class MasterImportToFinderTransformer implements DataTransformerInterface
{
    private $finder;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    public function transform($val)
    {
        return '';
    }

    public function reverseTransform($file)
    {
        if (!$file) {
            return null;
        }

        $failed = array();
        $file = $file->openFile();
        $file->setFlags(\SplFileObject::READ_CSV);
        foreach ($file as $row) {
            if (isset($row[1])) {
                $catalog = $row[0];
                $args = array_slice($row, 1);
                $catalog = $this->finder->getCatalog($catalog);
                try {
                    $image = $catalog->importImage($args);
                    $imageId = $this->finder->addImage($image);
                    $this->finder->addSelectedImageResult($imageId);
                } catch (\ErrorException $e) {
                    error_log($e->getMessage());
                    $failed[] = $row;
                }
            }
        }

        return $this->finder;
    }
}
