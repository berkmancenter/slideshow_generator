<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class SlideshowType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
	{
        $builder
            ->add('name')
            ->add('slide_delay')
            ->add('display_info', 'checkbox', array('required' => false))
		;

		if ($builder->getData()->getId()) {
			$slideshowId = $builder->getData()->getId();

			$builder->add('slides', null, array(
				'expanded' => true,
				'query_builder' => function(EntityRepository $er) use ($slideshowId) {
					return $er->createQueryBuilder('s')->where('s.slideshow = ?1')->setParameter(1, $slideshowId);
				}
			));
		}
    }
}
