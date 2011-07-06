<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FindResultsType extends AbstractType
{

	private $imageChoices = array();
	private $personId;

	public function setImageChoices(array $imageChoices)
	{
		$this->imageChoices = $imageChoices;
	}

	public function setPersonId($personId)
	{
		$this->personId = $personId;
	}

    public function buildForm(FormBuilder $builder, array $options)
    {
		$imageType = new ImageChoiceType();
		$imageType->setChoices($this->imageChoices);

		$slideshowType = new SlideshowChoiceType();
		$slideshowType->setPersonId($this->personId);

        $builder->add('find', $imageType);
		$builder->add('slideshows', $slideshowType);
    }
}
