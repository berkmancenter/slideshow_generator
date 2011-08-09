<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Berkman\SlideshowBundle\Entity;

class FinderResultsType extends AbstractType
{

	private $imageResults;

	private $imageCollectionResults;

	public function setResults(array $results)
	{
        $imageResults = array();
        $imageCollectionResults = array();
		foreach ($results as $result) {
			if ($result instanceof Entity\Image) {
                $this->imageResults[] = $result;
			}
			elseif ($result instanceof Entity\Collection) {
                $this->imageCollectionResults[] = $result;
			}
		}
	}

    public function buildForm(FormBuilder $builder, array $options)
    {
        if (!empty($this->imageResults)) {
            $builder
                ->add('images', 'entity', array(
                    'class' => 'Berkman\\SlideshowBundle\\Entity\\Image',
                    'property' => 'thumbnailUrl',
                    'choices' => $this->imageResults,
                    'multiple' => true,
                    'expanded' => true
                ))
            ;
        }

        if (!empty($this->imageCollectionResults)) {
            $builder
                ->add('imageCollections', 'entity', array(
                    'class' => 'Berkman\\SlideshowBundle\\Entity\\Collection',
                    'property' => 'coverUrl',
                    'choices' => $this->imageCollectionResults,
                    'multiple' => true,
                    'expanded' => true
                ))
            ;
        }
    }

	public function getName()
	{
		return 'finderresults';
	}
}
