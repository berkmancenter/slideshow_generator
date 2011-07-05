<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FindResultsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
			->add('find', new ImageChoiceType(), $options)
			->add('slideshows', new SlideshowChoiceType(), $options)  
        ;
    }
}
