<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FindShow extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
		$data = $builder->getData();
        $builder
			->add('images', 'choice', array(
				'choices' => $data['choices'],
				'multiple' => true,
				'expanded' => true
			))
        ;
    }
}
