<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ImageChoiceType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
			->add('images', 'choice', array(
				'choices' => $options['data']['imageChoices'],
				'multiple' => true,
				'expanded' => true
			))
        ;
    }
}
