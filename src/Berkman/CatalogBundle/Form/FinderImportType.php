<?php

namespace Berkman\SlideshowBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ImportType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        /*foreach ($catalogs as $catalog) {
            if ($catalog->hasCustomImporter()) {
                $importForms[$catalog->getId()] = $this->createForm(new ImportType())->createView();
            }
        }*/
        $builder
            ->add('attachment', 'file')
        ;
    }

    public function getName()
    {
        return 'import';
    }
}
