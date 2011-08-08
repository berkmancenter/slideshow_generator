<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Berkman\SlideshowBundle\Entity;

class FindResultsType extends AbstractType
{

	private $imageResults = array();

	private $imageCollectionResults = array();

	public function setResults(array $results)
	{
		foreach ($results as $result) {
			if ($result instanceof Entity\Image) {
                $this->imageResults[] = $result;
			}
			elseif ($result instanceof Entity\ImageCollection) {
                $this->imageCollectionResults[] = $result;
			}
		}
	}

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
			->add('images', 'entity', array(
                'class' => 'Berkman\\SlideshowBundle\\Entity\\Image',
                'property' => 'thumbnailUrl',
				'choices' => $this->imageResults,
				'multiple' => true,
				'expanded' => true
			))
        ;

        $builder
			->add('imageCollections', 'entity', array(
                'class' => 'Berkman\\SlideshowBundle\\Entity\\ImageCollection',
                'property' => 'coverUrl',
				'choices' => $this->imageCollectionResults,
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
