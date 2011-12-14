<?php
namespace Berkman\CatalogBundle\Form\Finder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class SearchType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('keyword', null, array( 'label' => 'Keyword'))
            ->add('catalogs')
        ;
    }

    public function getName()
    {
        return 'finder_search';
    }
}
