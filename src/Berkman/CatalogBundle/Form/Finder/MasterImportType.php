<?php
namespace Berkman\CatalogBundle\Form\Finder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Berkman\CatalogBundle\Entity\Finder;

class MasterImportType extends AbstractType
{
    private $finder;

    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $transformer = new DataTransformer\MasterImportToFinderTransformer($this->finder);
        $builder->appendClientTransformer($transformer);
    }

    public function getName()
    {
        return 'master_import';
    }

    public function getParent(array $options)
    {
        return 'file';
    }
}
