<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
			->add('email', 'email')
			->add('message', 'textarea')
        ;
    }

	public function getName()
	{
		return 'feedback';
	}
}
