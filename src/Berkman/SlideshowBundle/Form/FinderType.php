<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class FinderType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('keyword', null, array( 'label' => 'Keyword'))
            ->add('catalogs', 'entity', array(
                    'class' => 'Berkman\\SlideshowBundle\\Entity\\Catalog',
                    'property' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                    'query_builder' => function(EntityRepository $er) { return $er->createQueryBuilder('c')->where('c.isSearchable = ?1')->setParameter(1, true); }
                )
            )
        ;
    }

    public function getName()
    {
        return 'finder';
    }
}
