<?php
namespace Berkman\CatalogBundle\Form\Finder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CustomImportType extends AbstractType
{
    private $finder;

    public function __construct($finder = null)
    {
        $this->finder = $finder;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        foreach ($this->finder->getCatalogs() as $catalog) {
            $builder
                ->add($catalog->getId() . '_import', 'file')
            ;
        }
    }

    public function getName()
    {
        return 'custom_import';
    }
}
