<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FindResultsType extends AbstractType
{

	private $imageChoices = array();

	public function setImageChoices(array $imageChoices)
	{
		$this->imageChoices = $imageChoices;
	}

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
			->add('images', 'choice', array(
				'choices' => $this->imageChoices,
				'multiple' => true,
				'expanded' => true
			))
        ;
    }

	public function getName()
	{
		return 'findresults';
	}
}
