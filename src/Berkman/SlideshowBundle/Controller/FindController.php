<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
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

	public function submitAction()
	{
		$request   = $this->getRequest();
		$images    = unserialize(base64_decode($request->getSession()->get('images')));
		$response = $this->redirect($this->generateUrl('slideshow_add_images'));

		$findResults = $request->get('findresults');
		if (!empty($findResults['images'])) {
			if (!$images) {
				$images = array();
			}
			$images = $images + $findResults['images'];
		}

		if (in_array($request->get('action'), array('next', 'previous'))) {
			$page = ($request->get('action') == 'next') ? $request->get('page') + 1 : $request->get('page') - 1;
			$response = $this->redirect($this->generateUrl('find_show', array(
				'repos' => $request->get('repos'),
				'keyword' => $request->get('keyword'),
				'page' => $page
			)));
		}

		$request->getSession()->set('images', base64_encode(serialize($images)));

		return $response;
	}
}
