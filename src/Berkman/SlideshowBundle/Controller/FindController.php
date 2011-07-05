<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Berkman\SlideshowBundle\Entity\Find;
use Berkman\SlideshowBundle\Form\FindType;
use Berkman\SlideshowBundle\Form\FindResultsType;

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
		$imageChoices = array();
		$slideshowChoices = array();
		$choices = array();

		if (!$repos) {
			throw $this->createNotFoundException('Unable to find Repo entity.');
		}

		$finder = new Find($keyword, $repos);
		$images = $finder->getResults(null, $page);

		foreach ($images as $image) {
			$imageChoices[strval($image)] = $image->getThumbnailUrl();
		}

		if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			$personId = $this->get('security.context')->getToken()->getUser()->getId();
			$slideshows = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->findBy(array(
				'person' => $personId
			));
			foreach ($slideshows as $slideshow) {
				$slideshowChoices[] = $slideshow->getId();
			}
		}

		$viewParams = array(
			'numResults' => $finder->getNumResults(),
			'form' => $this->createForm(new FindResultsType(), array('imageChoices' => $imageChoices, 'slideshowChoices' => $slideshowChoices))->createView()
		);

		return $this->render('BerkmanSlideshowBundle:Find:show.html.twig', $viewParams);
    }
}
