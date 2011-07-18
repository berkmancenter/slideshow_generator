<?php

namespace Berkman\FOSUserChildBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


class DefaultController extends Controller
{
    
    public function indexAction($name)
    {
        return $this->render('BerkmanFOSUserChildBundle:Default:index.html.twig', array('name' => $name));
    }
}
