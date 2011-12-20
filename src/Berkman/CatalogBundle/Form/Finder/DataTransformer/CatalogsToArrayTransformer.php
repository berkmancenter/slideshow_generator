<?php
namespace Berkman\CatalogBundle\Form\Finder\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\DataTransformerInterface;

use Berkman\CatalogBundle\Entity\Finder;
use Berkman\CatalogBundle\Catalog\CatalogManager;

class CatalogsToArrayTransformer implements DataTransformerInterface
{
    private $catalogManager;

    public function __construct(CatalogManager $catalogManager)
    {
        $this->catalogManager = $catalogManager;
    }

    public function transform($finder)
    {
        if (null === $finder) {
            return null;
        }

        $choices = array();

        foreach($finder as $catalog) {
            $choices[] = $catalog->getId();
        }

        return $choices;
    }

    public function reverseTransform($array)
    {
        if (!$array) {
            return null;
        }

        $catalogs = array();

        foreach ($array as $catalogId) {
            $catalogs[] = $this->catalogManager->getCatalog($catalogId);
        }

        return $catalogs;
    }
}
