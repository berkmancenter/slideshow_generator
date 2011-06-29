<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Berkman\SlideshowBundle\Form\FindType;
use Berkman\SlideshowBundle\Form\FindShow;
use Berkman\SlideshowBundle\Entity\Find;

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
				$repos = array();
				foreach ($formData['repos'] as $repo) {
					$repos[] = $repo->getId();
				}
				return $this->redirect($this->generateUrl('find_show', array(
					'repos' => implode('+', $repos),
					'keyword' => $formData['keyword'],
					'page' => 1
				)));
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
    public function showAction($repos, $keyword, $page = 1)
    {
		$em = $this->getDoctrine()->getEntityManager();
		$repos = $em->getRepository('BerkmanSlideshowBundle:Repo')->findBy(array(
			'id' => explode('+', $repos)
		));

		$images = array();
		$imagesForView = array();
		$choices = array();

		if (!$repos) {
			throw $this->createNotFoundException('Unable to find Repo entity.');
		}

		$finder = new Find($keyword, $repos);
		$images = $finder->getResults(null, $page);
		$numResults = $finder->getNumResults();

		foreach ($images as $image) {
			$key = base64_encode($image->getId1().'|'.$image->getId2().'|'.$image->getId3().'|'.$image->getFromRepo()->getId());
			$choices[$key] = $image->getImageUrl();
		}

		return $this->render('BerkmanSlideshowBundle:Find:show.html.twig', array(
			'images' => $imagesForView,
			'numResults' => $numResults,
			'form' => $this->createForm(new FindShow(), array('choices' => $choices))->createView()
		));
    }
}
