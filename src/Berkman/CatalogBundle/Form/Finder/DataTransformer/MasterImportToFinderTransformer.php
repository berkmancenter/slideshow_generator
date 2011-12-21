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

    }
}
