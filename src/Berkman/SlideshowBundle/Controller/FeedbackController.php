<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Berkman\SlideshowBundle\Form\FeedbackType;

class FeedbackController extends Controller
{

    public function indexAction()
    {
        $form = $this->createForm(new FeedbackType());
        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Slideshow Generator Feedback')
                    ->setFrom('jclark.symfony@gmail.com')
                    ->setTo('jclark+test@cyber.law.harvard.edu')
                    ->setBody($this->renderView('BerkmanSlideshowBundle:Feedback:email.txt.twig', array(
                        'message' => $form['message']->getData(),
                        'email' => $form['email']->getData()
                    )))
                ;

                $this->get('mailer')->send($message);

                $request->getSession()->setFlash('notice', 'Your feedback has been sent. Thank you!');

                return $this->redirect($this->generateUrl('BerkmanSlideshowBundle_homepage'));
            }
        }

        return $this->render('BerkmanSlideshowBundle:Feedback:index.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
