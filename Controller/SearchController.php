<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Berkman\SlideshowBundle\Form\Search;

/**
 * Search controller.
 *
 */
class SearchController extends Controller
{
    /**
     * Show the search form.
     *
     */
    public function indexAction()
    {
        $request = $this->get('request');
        $form = $this->createForm(new Search());

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                return $this->redirect($this->generateUrl('repo_show', array('keyword' => $form->getAttribute('keyword'))));
            }
        }
		else {
			return $this->render('BerkmanSlideshowBundle:Search:index.html.twig', array(
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
		$images = array();
        $em = $this->getDoctrine()->getEntityManager();

        $repos = $em->getRepository('BerkmanSlideshowBundle:Repo')->findAll();

        if (!$repos) {
            throw $this->createNotFoundException('Unable to find Repo entity.');
        }

		foreach ($repos as $repo) {
			$images += $repo->search($keyword);
		}

		return print_r($images, TRUE);

        /*return $this->render('BerkmanSlideshowBundle:Search:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
		));*/
    }
}
