<?php
namespace Berkman\CatalogBundle\Form\Finder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CustomImportType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add($options['catalog']->getId() . '_import', 'file');
    }

    public function getName()
    {
        return 'custom_import';
    }

    public function getDefaultOptions(array $options)
    {
        return array('catalog' => null);
    }
}
