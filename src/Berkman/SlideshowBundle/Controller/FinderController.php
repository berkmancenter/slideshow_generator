<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Berkman\SlideshowBundle\Entity;
use Berkman\SlideshowBundle\Form\FinderType;
use Berkman\SlideshowBundle\Form\FinderResultsType;

/**
 * Finder controller.
 *
 */
class FinderController extends Controller
{
    /**
     * Show the search form.
     *
     */
    public function indexAction()
    {
        $request = $this->getRequest();

		$finder = new Entity\Finder;
        $finderForm = $this->createForm(new FinderType(), $finder);

        if ('POST' === $request->getMethod()) {
            $finderForm->bindRequest($request);

            if ($finderForm->isValid()) {
				$repoIds = array();
				$repos = $finder->getRepos();
				foreach ($repos as $repo) {
					$repoIds[] = $repo->getId();
				}

				return $this->redirect($this->generateUrl('finder_show', array(
					'repos' => implode('_', $repoIds),
					'keyword' => $finder->getKeyword(),
					'page' => 1
				)));
            }
        }
		else {
			return $this->render('BerkmanSlideshowBundle:Finder:index.html.twig', array(
				'finderForm'   => $finderForm->createView()
			));
		}

    }

    /**
     * Show the search results.
     *
     * Note: These two functions pretty much repeat each other - they shouldn't
     */
    public function showAction($repos, $keyword, $page = 1)
    {
		$em = $this->getDoctrine()->getEntityManager();
		$finderResultsFormType = new FinderResultsType();

		$repos = $em->getRepository('BerkmanSlideshowBundle:Repo')->findBy(array(
			'id' => explode('_', $repos)
		));
		if (!$repos) {
			throw $this->createNotFoundException('Unable to find Repos.');
		}

        $finder = $this->getFinder();
        $finder->setRepos($repos);

		$output = $finder->findResults($keyword, $page);
        //Not sure why this is necessary - it should be cascading.
        foreach ($output['results'] as $result) {
            $em->persist($result);
        }

        $em->persist($finder);
        $em->flush();

		$finderResultsFormType->setResults($output['results']);

		return $this->render('BerkmanSlideshowBundle:Finder:show.html.twig', array(
            'finder' => $finder,
            'form' => $this->createForm($finderResultsFormType)->createView(),
        ));
    }

    /**
     * Show the results of a collection search
     *
     */
    public function showCollectionAction($collectionId, $page = 1)
    {
		$em = $this->getDoctrine()->getEntityManager();
		$finderResultsFormType = new FinderResultsType();

		$collection = $em->getRepository('BerkmanSlideshowBundle:Collection')->find($collectionId);
		if (!$collection) {
			throw $this->createNotFoundException('Unable to find Collection.');
		}

        $finder = $this->getFinder();
		$output = $finder->findCollectionResults($collection, $page);
		$finderResultsFormType->setResults($output['results']);
        foreach ($output['results'] as $result) {
            $em->persist($result);
        }

        $em->persist($finder);
        $em->flush();

		return $this->render('BerkmanSlideshowBundle:Finder:show.html.twig', array(
            'finder' => $finder,
            'form' => $this->createForm($finderResultsFormType)->createView(),
        ));
    }

	public function submitAction()
	{
		$request = $this->getRequest();
        $em = $this->getDoctrine()->getEntityManager();

        $finder = $this->getFinder();
		$response  = $this->redirect($this->generateUrl('slideshow_add_images'));

		$results = $request->get('finderresults');
		if (!empty($results['images'])) {
            foreach ($results['images'] as $image_id) {
                $finder->addSelectedImageResults($em->find('BerkmanSlideshowBundle:Image', $image_id)); 
            }
		}

        $em->persist($finder);
        $em->flush();

        $repoIds = array();
        $repos = $finder->getRepos();
        foreach ($repos as $repo) {
            $repoIds[] = $repo->getId();
        }

		if (in_array($request->get('action'), array('Next Page', 'Previous Page'))) {
            $page = ($request->get('action') == 'Next Page') ?
                $finder->getCurrentPage() + 1 : $finder->getCurrentPage() - 1;
			$response = $this->redirect($this->generateUrl('finder_show', array(
				'repos' => implode('_', $repoIds),
				'keyword' => $finder->getKeyword(),
				'page' => $page
			)));
		}
        elseif ($request->get('action') != 'Finish') {
            $response = $this->redirect($this->generateUrl('finder_collection', array(
                'collectionId' => $request->get('action')
            )));
        }

		return $response;
	}

    private function getFinder()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $finderId = $this->getRequest()->getSession()->get('finder_id');
        if ($finderId) {
            $finder = $em->getRepository('BerkmanSlideshowBundle:Finder')->find($finderId);
        }
        else {
            $finder = new Entity\Finder();
            $em->persist($finder);
            $em->flush();
            $this->getRequest()->getSession()->set('finder_id', $finder->getId());
        }

        return $finder;
    }
}
