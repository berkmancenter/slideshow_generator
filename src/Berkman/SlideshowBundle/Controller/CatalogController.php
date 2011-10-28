<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Berkman\SlideshowBundle\Entity\Catalog;
use Berkman\SlideshowBundle\Entity\Finder;
use Berkman\SlideshowBundle\Entity\Batch;
use Berkman\SlideshowBundle\Form\CatalogType;
use Berkman\SlideshowBundle\Form\ImportType;

/**
 * Catalog controller.
 *
 */
class CatalogController extends Controller
{
    /**
     * Lists all Catalog entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('BerkmanSlideshowBundle:Catalog')->findAll();

        return $this->render('BerkmanSlideshowBundle:Catalog:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finders and displays a Catalog entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Catalog')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Catalog entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BerkmanSlideshowBundle:Catalog:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new Catalog entity.
     *
     */
    public function newAction()
    {
        $entity = new Catalog();
        $form   = $this->createForm(new CatalogType(), $entity);

        return $this->render('BerkmanSlideshowBundle:Catalog:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Catalog entity.
     *
     */
    public function createAction()
    {
        $entity  = new Catalog();
        $request = $this->getRequest();
        $form    = $this->createForm(new CatalogType(), $entity);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity->setCreated(new \DateTime('now'));
                $entity->setUpdated(new \DateTime('now'));
                $em->persist($entity);
                $em->flush();
                $request->getSession()->setFlash('notice', 'Catalog "' . $entity->getName() . '" was successfully created.');

                return $this->redirect($this->generateUrl('catalog_show', array('id' => $entity->getId())));
                
            }
        }

        return $this->render('BerkmanSlideshowBundle:Catalog:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Catalog entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Catalog')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Catalog entity.');
        }

        $editForm = $this->createForm(new CatalogType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BerkmanSlideshowBundle:Catalog:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Catalog entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Catalog')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Catalog entity.');
        }

        $editForm   = $this->createForm(new CatalogType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity->setUpdated(new \DateTime('now'));
                $em->persist($entity);
                $em->flush();
                $request->getSession()->setFlash('notice', 'Catalog "' . $entity->getName() . '" was successfully updated.');

                return $this->redirect($this->generateUrl('catalog_edit', array('id' => $id)));
            }
        }

        return $this->render('BerkmanSlideshowBundle:Catalog:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Catalog entity.
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
                $entity = $em->getRepository('BerkmanSlideshowBundle:Catalog')->find($id);

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Catalog entity.');
                }

                $em->remove($entity);
                $em->flush();
                $request->getSession()->setFlash('notice', 'Catalog "' . $entity->getName() . '" was successfully deleted.');
            }
        }

        return $this->redirect($this->generateUrl('catalog'));
    }

    public function getProgressAction()
    {
        $session = $this->getRequest()->getSession();
        return $this->render('BerkmanSlideshowBundle:Catalog:progress.json.twig', array(
            'progress' => $session->get('progress')
        ));
    }

    public function importAction($id)
    {
        $request = $this->getRequest();
        if ('POST' === $request->getMethod()) {
            $finder = $this->getFinder();
            $em = $this->getDoctrine()->getEntityManager();
            $catalog = $em->getRepository('BerkmanSlideshowBundle:Catalog')->find($id);
            if (!$catalog) {
                throw $this->createNotFoundException('Unable to find Catalog entity.');
            }

            $importForm = $this->createForm(new ImportType());
            $importForm->bindRequest($request);
            if ($importForm->isValid()) {
                $file = $importForm['attachment']->getData();

                $file = $file->openFile();
                $batch = new Batch($file, $request->getSession());
                $images = $catalog->getFetcher()->getImagesFromImport($batch);

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
