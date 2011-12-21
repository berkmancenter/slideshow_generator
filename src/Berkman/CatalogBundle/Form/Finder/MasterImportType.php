<?php
namespace Berkman\CatalogBundle\Form\Finder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Berkman\CatalogBundle\Entity\Finder;

class MasterImportType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('file', 'file');
    }

    public function getName()
    {
        return 'master_import';
    }
}
