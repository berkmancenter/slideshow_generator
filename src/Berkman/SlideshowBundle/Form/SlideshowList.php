<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class SlideshowList extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$data = $builder->getData();
		$builder->add('slideshows', 'entity', array(
			'class' => 'Berkman\\SlideshowBundle\\Entity\\Slideshow',
			'query_builder' => function(EntityRepository $er) {
				return $er->createQueryBuilder('s')
					->where('u.person_id = ?1')
					->setParameter(1, $data['personId']);
			}
		));
	}
}
