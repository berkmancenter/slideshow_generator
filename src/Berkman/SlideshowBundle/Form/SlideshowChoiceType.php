<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class SlideshowChoiceType extends AbstractType
{

	private $personId;

	public function setPersonId($personId)
	{
		$this->personId = $personId;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$personId = $this->personId;
		$builder->add('slideshows', 'entity', array(
			'class' => 'Berkman\\SlideshowBundle\\Entity\\Slideshow',
			'property' => 'name',
			'multiple' => true,
			'expanded' => true,
			'query_builder' => function(EntityRepository $er) use ($personId) {
				$qb = $er->createQueryBuilder('s');
				return $qb->where('s.person = ?1')->setParameter(1, $personId);
			}
		));
	}

	public function getName()
	{
		return 'slideshowchoice';
	}
}
