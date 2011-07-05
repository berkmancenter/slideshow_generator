<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class SlideshowChoiceType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('slideshows', 'entity', array(
			'class' => 'Berkman\\SlideshowBundle\\Entity\\Slideshow',
			'property' => 'name',
			'choices' => $options['data']['slideshowChoices'],
			'required' => false
		));
	}
}
