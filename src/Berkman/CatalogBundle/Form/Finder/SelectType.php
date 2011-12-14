<?php
namespace Berkman\SlideshowBundle\Form\Finder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class FinderType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('keyword', null, array( 'label' => 'Keyword'))
        ;
    }

    public function getName()
    {
        return 'finder_select';
    }
}
