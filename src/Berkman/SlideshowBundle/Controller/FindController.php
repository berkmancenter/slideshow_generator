<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Berkman\SlideshowBundle\Entity as Entity;
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
		$finder = new Entity\Find();
        $form = $this->createForm(new FindType(), $finder);
        $request = $this->get('request');

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
				$repos = $finder->getRepos();
				$repoIds = array();
				foreach ($repos as $repo) {
					$repoIds[] = $repo->getId();
				}
				return $this->redirect($this->generateUrl('find_show', array(
					'repos' => implode('+', $repoIds),
					'keyword' => $finder->getKeyword(),
					'page' => 1
				)));
            }
        }
		else {
			return $this->render('BerkmanSlideshowBundle:Find:index.html.twig', array(
				'findForm'   => $form->createView()
			));
		}
    }

    /**
     * Show the search results.
     *
     */
    public function showAction($repos, $keyword, $page = 1)
    {
		$images = array();
		$imageChoices = array();
		$findResults = new FindResultsType();

		$em = $this->getDoctrine()->getEntityManager();

		$reposString = $repos;
		$repos = $em->getRepository('BerkmanSlideshowBundle:Repo')->findBy(array(
			'id' => explode('+', $repos)
		));

		if (!$repos) {
			throw $this->createNotFoundException('Unable to find Repos.');
		}

		$finder = new Entity\Find($repos);
		$images = $finder->getImages($keyword, $page);

		foreach ($images as $image) {
			$imageChoices[strval($image)] = $image->getThumbnailUrl();
		}

		if ($this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			$personId = $this->get('security.context')->getToken()->getUser()->getId();
			$findResults->setPersonId($personId);
		}

		$findResults->setImageChoices($imageChoices);

		$viewParams = array(
			'totalResults' => $finder->getTotalResults(),
			'form' => $this->createForm($findResults)->createView(),
			'keyword' => $keyword,
			'repos' => $reposString,
			'page' => $page
		);

		return $this->render('BerkmanSlideshowBundle:Find:show.html.twig', $viewParams);
    }

	public function resultsToSlideshowAction()
	{
		$em = $this->getDoctrine()->getEntityManager();
		$request = $this->getRequest();

		if ('POST' === $request->getMethod()) {
			$findResults = $request->request->get('findresults');
			$images = $findResults['find']['images'];
			$slideshowId = $findResults['slideshows']['slideshows'];
			if (empty($slideshowId)) {
				$slides = array();	
				foreach ($images as $image) {
					$image = unserialize(base64_decode($image));
					$repo = $em->getRepository('BerkmanSlideshowBundle:Repo')->find($image['fromRepo']);
					$image = new Entity\Image($repo, $image['id1'], $image['id2'], $image['id3'], $image['id4']);
					$em->persist($image);
					$slide = new Entity\Slide();
					$slide->setImage($image);
					$em->persist($slide);
					$slides[] = $slide;
				}
				$em->flush();

				$response = $this->forward('BerkmanSlideshowBundle:Slideshow:create', array('slides' => $slides));
			}
			else {
				$slideshow = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($slideshowId);

				foreach ($images as $image) {
					$image = unserialize(base64_decode($image));
					$repo = $em->getRepository('BerkmanSlideshowBundle:Repo')->find($image['fromRepo']);
					$image = new Entity\Image($repo, $image['id1'], $image['id2'], $image['id3'], $image['id4'], $image['id5'], $image['id6']);
					$em->persist($image);
					$slide = new Entity\Slide();
					$slide->setImage($image);
					$slideshow->addSlide($slide);
				}
				$em->persist($slideshow);
				$em->flush();

				$response = $this->redirect($this->generateUrl('slideshow_show', array('id' => $slideshowId)));

			}
		}

		return $response;
	}
}
