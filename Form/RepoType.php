<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class RepoType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('name')
            ->add('search_url_pattern')
            ->add('record_url_pattern')
            ->add('image_url_pattern')
            ->add('metadata_url_pattern')
            ->add('result_code')
        ;
    }
}