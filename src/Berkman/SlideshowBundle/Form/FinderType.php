<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FinderType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
			->add('keyword')
			->add('repos', 'entity', array(
					'class' => 'Berkman\\SlideshowBundle\\Entity\\Repo',
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
