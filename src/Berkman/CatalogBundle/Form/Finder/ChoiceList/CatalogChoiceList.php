<?php

namespace Berkman\CatalogBundle\Form\Finder\ChoiceList;

use Symfony\Component\Form\Extension\Core\ChoiceList\ArrayChoiceList;
use Berkman\CatalogBundle\Catalog\CatalogManager;

class CatalogChoiceList extends ArrayChoiceList {

    private $catalogManager;

    public function __construct(CatalogManager $catalogManager, $choices = array())
    {
        $this->catalogManager = $catalogManager;
        parent::__construct($choices);
    }

    protected function load()
    {
        parent::load();

        foreach ($this->catalogManager->getCatalogs() as $catalog) {
            if ($catalog->hasImageSearch() || $catalog->hasImageGroupSearch()) {
                $this->choices[$catalog->getId()] = $catalog->getName();
            }
        }

        return $this->choices;
    }
}
