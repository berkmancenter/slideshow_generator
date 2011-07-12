<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Berkman\SlideshowBundle\Form\FindType;

class DefaultController extends Controller
{
    
    public function indexAction()
    {
		$em = $this->getDoctrine()->getEntityManager();
		$slideshows = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->findAll();
        $form = $this->createForm(new FindType());
		
		return $this->render('BerkmanSlideshowBundle:Default:index.html.twig', array(
			'slideshows' => $slideshows,
			'findForm'   => $form->createView()
		));
    }
}
