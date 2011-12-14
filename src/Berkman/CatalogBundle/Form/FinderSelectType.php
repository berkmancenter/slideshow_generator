<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class FinderType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        /*$catalogChoices = array();
        foreach ($catalogs as $catalog) {
            if ($catalog->hasImageSearch() || $catalog->hasImageGroupSeach()) {
                $catalogChoices[$catalog->getId()] = $catalog->getName();
            }
        }*/

        $builder
            ->add('keyword', null, array( 'label' => 'Keyword'))
            ->add('catalogs', 'choice', array(
                    'choices' => $options['data']['choices'],
                    'multiple' => true,
                    'expanded' => true
                )
            )
        ;
    }

    public function getName()
    {
        return 'finder';
    }
}
