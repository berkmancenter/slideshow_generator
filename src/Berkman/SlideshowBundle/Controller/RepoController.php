<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Berkman\SlideshowBundle\Entity\Repo;
use Berkman\SlideshowBundle\Entity\Finder;
use Berkman\SlideshowBundle\Entity\Batch;
use Berkman\SlideshowBundle\Form\RepoType;
use Berkman\SlideshowBundle\Form\ImportType;

/**
 * Repo controller.
 *
 */
class RepoController extends Controller
{
    /**
     * Lists all Repo entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('BerkmanSlideshowBundle:Repo')->findAll();

        return $this->render('BerkmanSlideshowBundle:Repo:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finders and displays a Repo entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Repo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Repo entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BerkmanSlideshowBundle:Repo:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Repo entity.
     *
     */
    public function newAction()
    {
        $entity = new Repo();
        $form   = $this->createForm(new RepoType(), $entity);

        return $this->render('BerkmanSlideshowBundle:Repo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Repo entity.
     *
     */
    public function createAction()
    {
        $entity  = new Repo();
        $request = $this->getRequest();
        $form    = $this->createForm(new RepoType(), $entity);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity->setCreated(new \DateTime('now'));
                $entity->setUpdated(new \DateTime('now'));
                $em->persist($entity);
                $em->flush();
                $request->getSession()->setFlash('notice', 'Repo "' . $entity->getName() . '" was successfully created.');

                return $this->redirect($this->generateUrl('repo_show', array('id' => $entity->getId())));
                
            }
        }

        return $this->render('BerkmanSlideshowBundle:Repo:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Repo entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Repo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Repo entity.');
        }

        $editForm = $this->createForm(new RepoType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BerkmanSlideshowBundle:Repo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Repo entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Repo')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Repo entity.');
        }

        $editForm   = $this->createForm(new RepoType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity->setUpdated(new \DateTime('now'));
                $em->persist($entity);
                $em->flush();
                $request->getSession()->setFlash('notice', 'Repo "' . $entity->getName() . '" was successfully updated.');

                return $this->redirect($this->generateUrl('repo_edit', array('id' => $id)));
            }
        }

        return $this->render('BerkmanSlideshowBundle:Repo:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Repo entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity = $em->getRepository('BerkmanSlideshowBundle:Repo')->find($id);

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Repo entity.');
                }

                $em->remove($entity);
                $em->flush();
                $request->getSession()->setFlash('notice', 'Repo "' . $entity->getName() . '" was successfully deleted.');
            }
        }

        return $this->redirect($this->generateUrl('repo'));
    }

    public function getProgressAction()
    {
        $session = $this->getRequest()->getSession();
        return $this->render('BerkmanSlideshowBundle:Repo:progress.json.twig', array(
            'progress' => $session->get('progress')
        ));
    }

    public function importAction($id)
    {
		$request = $this->getRequest();
        if ('POST' === $request->getMethod()) {
            $finder = $this->getFinder();
            $em = $this->getDoctrine()->getEntityManager();
            $repo = $em->getRepository('BerkmanSlideshowBundle:Repo')->find($id);
            if (!$repo) {
                throw $this->createNotFoundException('Unable to find Repo entity.');
            }

            $importForm = $this->createForm(new ImportType());
            $importForm->bindRequest($request);
            if ($importForm->isValid()) {
                $file = $importForm['attachment']->getData();

                $file = $file->openFile();
                $batch = new Batch($file, $request->getSession());
                $images = $repo->getFetcher()->getImagesFromImport($batch);

                foreach ($images as $image) {
                    $imageId = $finder->addImage($image);
                    $finder->addSelectedImageResult($imageId);
                }

                $this->setFinder($finder);

                return $this->redirect($this->generateUrl('slideshow_add_images'));
            }
        }
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }

    /**
     * Get the Finder object for the current session
     * or make a new one.
     *
     * @return Berkman\SlideshowBundle\Entity\Finder
     */
    private function getFinder()
    {
        $finder = $this->getRequest()->getSession()->get('finder');
        if (!$finder) {
            $finder = new Finder();
            $this->setFinder($finder);
        }

        return $finder;
    }

    /**
     * Assign some Finder object to the current session
     *
     * @param Berkman\SlideshowBundle\Entity\Finder $finder
     */
    private function setFinder(Finder $finder)
    {
        $this->getRequest()->getSession()->set('finder', $finder);
    }
}
