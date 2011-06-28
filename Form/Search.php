<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class Search extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
			->add('keyword', 'text')
			->add('repos', 'entity', array(
					'class' => 'Berkman\\SlideshowBundle\\Entity\\Repo',
					'property' => 'name',
					'expanded' => true,
					'multiple' => true
				)
			)
        ;
    }
}
