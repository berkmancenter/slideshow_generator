<?php
namespace Berkman\CatalogBundle\Form\Finder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;
use Berkman\CatalogBundle\Entity\Finder;
use Berkman\CatalogBundle\Form\Finder\DataTransformer\CatalogsToArrayTransformer;
use Berkman\CatalogBundle\Form\Finder\ChoiceList\CatalogChoiceList;

class CatalogSelectorType extends AbstractType
{

    public function buildForm(FormBuilder $builder, array $options)
    {
        $transformer = new CatalogsToArrayTransformer($options['catalog_manager']);
        $builder->prependClientTransformer($transformer);
    }

    public function getParent(array $options)
    {
        return 'choice';
    }

    public function getDefaultOptions(array $options)
    {
        $defaultOptions = array(
            'multiple'          => true,
            'expanded'          => true,
            'catalog_manager'   => null
        );

        $options = array_replace($defaultOptions, $options);

        $defaultOptions['choice_list'] = new CatalogChoiceList($options['catalog_manager']);
        if (is_object($options['data'])) {
            $defaultOptions['data'] = $options['data']->getCatalog();
        }
        return $defaultOptions;
    }

    public function getName()
    {
        return 'catalog_selector';
    }
}
