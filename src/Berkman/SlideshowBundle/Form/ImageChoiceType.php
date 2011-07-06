<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ImageChoiceType extends AbstractType
{

	private $choices = array('none');

	public function setChoices(array $choices)
	{
		if (!empty($choices)) {
			$this->choices = $choices;
		}
	}

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
			->add('images', 'choice', array(
				'choices' => $this->choices,
				'multiple' => true,
				'expanded' => true
			))
        ;
    }
}
