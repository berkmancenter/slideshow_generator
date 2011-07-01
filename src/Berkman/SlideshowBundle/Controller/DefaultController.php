<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction()
    {
        return $this->redirect($this->generateUrl('find'));
    }
}
