<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Berkman\SlideshowBundle\Form\FindType;

/**
 * Find controller.
 *
 */
class FindController extends Controller
{
    /**
     * Show the search form.
     *
     */
    public function indexAction()
    {
        $request = $this->get('request');
        $form = $this->createForm(new FindType());

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
				$formData = $form->getData();

				$images = array();
				$viewImages = array();

				$repos = $formData['repos'];

				if (!$repos) {
					throw $this->createNotFoundException('Unable to find Repo entity.');
				}

				foreach ($repos as $repo) {
					$images += $repo->search($formData['keyword']);
				}

				foreach ($images as $image) {
					$viewImages[] = array(
						'url' => $image->getImageUrl(),
						'value' => ''
					);
				}


				return $this->render('BerkmanSlideshowBundle:Find:show.html.twig', array(
					'images' => $viewImages
				));
            }
        }
		else {
			return $this->render('BerkmanSlideshowBundle:Find:index.html.twig', array(
				'form'   => $form->createView()
			));
		}
    }

    /**
     * Show the search results.
     *
     */
    public function showAction($keyword)
    {

        /*return $this->render('BerkmanSlideshowBundle:Find:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
		));*/
    }
}
