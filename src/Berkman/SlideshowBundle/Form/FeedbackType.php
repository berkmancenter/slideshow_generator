<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('email', 'email', array('label' => 'Your email address'))
            ->add('message', 'textarea', array('label' => 'Your message'))
            ->add('cc', 'checkbox', array('label' => 'CC yourself?'))
        ;
    }

    public function getName()
    {
        return 'feedback';
    }
}
