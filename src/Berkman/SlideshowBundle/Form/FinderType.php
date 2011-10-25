<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

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
