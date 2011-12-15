<?php
namespace Berkman\CatalogBundle\Form\Finder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;
use Berkman\CatalogBundle\Entity\Finder;
use Berkman\CatalogBundle\Form\Finder\DataTransformer\FinderToCatalogsTransformer;

class CatalogSelectorType extends AbstractType
{
    /*private $choices;

    public function __construct(Finder $finder)
    {
        foreach($finder->getCatalogs() as $catalog) {
            if ($catalog->hasImageSearch() || $catalog->hasImageGroupSearch()) {
                $this->choices[$catalog->getId()] = $catalog->getName();
            }
        }
    }
     */

    public function buildForm(FormBuilder $builder, array $options)
    {
        $transformer = new FinderToCatalogsTransformer();
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
            'choices'           => 
        );

        $options = array_replace($defaultOptions, $options);
        return $defaultOptions;
    }

    public function getName()
    {
        return 'catalog_selector';
    }
}
