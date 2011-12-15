<?php
namespace Berkman\CatalogBundle\Form\Finder\DataTransformer;

use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\DataTransformerInterface;

use Berkman\CatalogBundle\Entity\Finder;

class FinderToCatalogsTransformer implements DataTransformerInterface
{

    public function transform($catalogs)
    {
        if (null === $catalogs) {
            return null;
        }

        //var_dump($catalogs);
        $choices = array();

        foreach($catalogs as $catalog) {
            $choices[] = $catalog->getId();
        }

        return $choices;
    }

    public function reverseTransform($file)
    {
        if (!$file) {
            return null;
        }
        var_dump($file); exit;

        return $this->finder;
    }
}
