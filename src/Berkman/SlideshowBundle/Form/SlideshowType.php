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
            ->add('name', null, array('label' => 'Slideshow Name'))
            ->add('slide_delay', null, array('label' => 'Slide Delay (seconds)'))
            ->add('always_show_info', 'checkbox', array('required' => false))
			->add('display_controls', 'checkbox', array('required' => false))
		;

		if ($builder->getData()->getId()) {
			$slideshowId = $builder->getData()->getId();

			$builder->add('slides', null, array(
				'expanded' => true,
				'query_builder' => function(EntityRepository $er) use ($slideshowId) {
					return $er->createQueryBuilder('s')->where('s.slideshow = ?1')->orderBy('s.position', 'ASC')->setParameter(1, $slideshowId);
				}
			));
		}
    }

	public function getName()
	{
		return 'slideshow';
	}
}
