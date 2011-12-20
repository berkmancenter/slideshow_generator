<?php
namespace Berkman\CatalogBundle\Form\Finder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;
use Berkman\CatalogBundle\Entity\Finder;
use Berkman\CatalogBundle\Catalog\CatalogManager;

class SearchType extends AbstractType
{
    private $catalogManager;

    public function __construct(CatalogManager $catalogManager)
    {
        $this->catalogManager = $catalogManager;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder
            ->add('keyword', null, array( 'label' => 'Keyword'))
            ->add('catalogs', 'catalog_selector', array('catalog_manager' => $this->catalogManager))
        ;
    }

    public function getName()
    {
        return 'finder_search';
    }
}
