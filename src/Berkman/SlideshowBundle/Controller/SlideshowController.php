<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Berkman\SlideshowBundle\Entity\Slideshow;
use Berkman\SlideshowBundle\Entity\Slide;
use Berkman\SlideshowBundle\Entity\Finder;
use Berkman\SlideshowBundle\Form\SlideshowType;
use Berkman\SlideshowBundle\Form\SlideshowChoiceType;
use Berkman\SlideshowBundle\Form\ImportType;
use Berkman\SlideshowBundle\Fetcher\ImportFetcherInterface;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Slideshow controller.
 *
 */
class SlideshowController extends Controller
{
    /**
     * Finds and displays a Slideshow entity.
     *
     * @param integer $id  Slideshow id
     * @return Symfony Response
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slideshow entity.');
        }

        $args = array(
            'entity'    => $entity,
            'canEdit'   => false,
            'canDelete' => false
        );

        $securityContext = $this->get('security.context');

        if  ($securityContext->isGranted('EDIT', $entity) === true) {
            $args['canEdit'] = true;
        }

        if ($securityContext->isGranted('DELETE', $entity) === true) {
            $deleteForm = $this->createDeleteForm($id);
            $args['canDelete'] = true;
            $args['delete_form'] = $deleteForm->createView();
        }

        return $this->render('BerkmanSlideshowBundle:Slideshow:show.html.twig', $args);
    }

    public function slideTilesAction($id)
    {
        $response = new Response();
        $response->setPublic();
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slideshow entity.');
        }

        $updatedAt = $entity->getUpdated();
        $response->setLastModified($updatedAt);
        $response->setETag(md5($updatedAt->format('U')));

        if ($response->isNotModified($this->getRequest())) {

            return $response;
        } 
        else {
            $images = array();
            foreach ($entity->getSlides() as $slide) {
                $images[] = $slide->getImage();
            }
            return $this->render(
                'BerkmanSlideshowBundle:Slideshow:slideTiles.html.twig',
                array('images' => $images),
                $response
            );
        }
    }

    /**
     * Displays the actual slideshow
     *
     * @param integer $id  Slideshow id
     * @return Symfony Response
     */
    public function slideshowAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $response = new Response();
        $response->setPublic();

        $slideshow = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($id);

        if (!$slideshow) {
            throw $this->createNotFoundException('Unable to find Slideshow.');
        }

        $updatedAt = $slideshow->getUpdated();
        $response->setLastModified($updatedAt);

        if ($response->isNotModified($this->getRequest())) {
            return $response;
        }
        else {
            return $this->render(
                'BerkmanSlideshowBundle:Slideshow:slideshow.html.twig',
                array('slideshow' => $slideshow),
                $response
            );
        }
    }

    /**
     * Displays a form to create a new Slideshow entity.
     *
     * @return Symfony Response
     */
    public function newAction()
    {
        $entity = new Slideshow();
        $form   = $this->createForm(new SlideshowType(), $entity);

        return $this->render('BerkmanSlideshowBundle:Slideshow:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Slideshow entity.
     *
     * @return Symfony Response
     */
    public function createAction()
    {
        $slideshow  = new Slideshow();
        $request = $this->getRequest();
        $form    = $this->createForm(new SlideshowType(), $slideshow);

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $user = $this->get('security.context')->getToken()->getUser();
                $slideshow->setPerson($user);
                $slideshow->setCreated(new \DateTime('now'));
                $finder = $this->getFinder();

                if ($finder) {
                    $images = $finder->getSelectedImageResults();
                    foreach ($images as $image) {
                        $newImage = clone $image;
                        $newImage->setFromCatalog($em->find('BerkmanSlideshowBundle:Catalog', $image->getFromCatalog()->getId()));
                        $slide = new Slide($newImage);
                        $slideshow->addSlide($slide);
                    }
                    $request->getSession()->remove('finder');
                }
                $slideshow->setUpdated(new \DateTime('now'));
                $em->persist($slideshow);
                $em->flush();
                $request->getSession()->setFlash('notice', 'New slideshow "' . $slideshow->getName() . '" created with ' . count($slideshow->getSlides()) . ' slides.');

                 // creating the ACL
                $aclProvider = $this->get('security.acl.provider');
                $objectIdentity = ObjectIdentity::fromDomainObject($slideshow);
                $acl = $aclProvider->createAcl($objectIdentity);

                // retrieving the security identity of the currently logged-in user
                $securityIdentity = UserSecurityIdentity::fromAccount($user);

                // grant owner access
                $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
                $aclProvider->updateAcl($acl);

                return $this->redirect($this->generateUrl('slideshow_show', array('id' => $slideshow->getId())));
            }
        }

        return $this->render('BerkmanSlideshowBundle:Slideshow:new.html.twig', array(
            'entity' => $slideshow,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Slideshow entity.
     *
     * @param integer $id  Slideshow id
     * @return Symfony Response
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $slideOrder = array();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slideshow entity.');
        }

        // check for edit access
        if (false === $this->get('security.context')->isGranted('EDIT', $entity))
        {
            throw new AccessDeniedException();
        }

        foreach ($entity->getSlides() as $slide) {
            $slideOrder[] = $slide->getImage()->getId();
        }

        $editForm = $this->createForm(new SlideshowType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BerkmanSlideshowBundle:Slideshow:edit.html.twig', array(
            'entity'      => $entity,
            'slide_order' => implode(',',$slideOrder),
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Slideshow entity.
     *
     * @param integer $id  Slideshow id
     * @return Symfony Response
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $entity = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($id);

        $oldSlideIds = $entity->getSlides()->map(function ($slide) { return $slide->getId(); })->toArray();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slideshow entity.');
        }

        // check for update access
        if (false === $this->get('security.context')->isGranted('EDIT', $entity))
        {
            throw new AccessDeniedException();
        }

        $editForm   = $this->createForm(new SlideshowType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                $slideCatalog = $em->getRepository('BerkmanSlideshowBundle:Slide');
                $newSlideIds = $entity->getSlides()->map(function ($slide) { return $slide->getId(); })->toArray();
                $slideIdsToRemove = array_diff($oldSlideIds, $newSlideIds);

                if ($slideIdsToRemove) {
                    $slidesToRemove = $slideCatalog->findById($slideIdsToRemove);
                    foreach ($slidesToRemove as $slide) {
                        $em->remove($slide);
                    }
                }

                foreach (explode(',', $request->get('slide_order')) as $position => $slideId) {
                    $slide = $slideCatalog->find($slideId);
                    $slide->setPosition($position + 1);
                    $em->persist($slide);
                }
                
                $entity->setUpdated(new \DateTime('now'));

                $em->persist($entity);
                $em->flush();

                $request->getSession()->setFlash('notice', 'Slideshow "' . $entity->getName() . '" successfully updated.');

                return $this->redirect($this->generateUrl('slideshow_edit', array('id' => $id)));
            }
        }

        return $this->render('BerkmanSlideshowBundle:Slideshow:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Slideshow entity.
     *
     * @param integer $id  Slideshow id
     * @return Symfony Response
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $form->bindRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $entity = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($id);
                $slides = $em->getRepository('BerkmanSlideshowBundle:Slide')->findBy(array(
                    'slideshow' => $id
                ));

                if (!$entity) {
                    throw $this->createNotFoundException('Unable to find Slideshow entity.');
                }

                // check for delete access
                if (false === $this->get('security.context')->isGranted('DELETE', $entity))
                {
                    throw new AccessDeniedException();
                }

                $request->getSession()->setFlash('notice', 'Slideshow "' . $entity->getName() . '" successfully deleted.');
                $em->remove($entity);
                $em->flush();
            }
        }

        return $this->redirect($this->generateUrl('BerkmanSlideshowBundle_homepage'));
    }

    /**
     * Add Images to a Slideshow
     *
     * @param integer The slideshow id to add images to
     * @return Symfony Response
     */
    public function addImagesAction()
    {
        $slideshow = new Slideshow();
        $request   = $this->getRequest();
        $em        = $this->getDoctrine()->getEntityManager();
        $finder    = $this->getFinder();
        $images    = $finder->getSelectedImageResults();

        $slideshowChoiceType = new SlideshowChoiceType();
        $slideshowChoiceType->setPersonId($this->get('security.context')->getToken()->getUser()->getId());
        $addImagesForm = $this->createForm($slideshowChoiceType);
        $newSlideshowForm = $this->createForm(new SlideshowType(), $slideshow);

        $response = $this->render('BerkmanSlideshowBundle:Slideshow:addImages.html.twig', array(
            'addImagesForm' => $addImagesForm->createView(),
            'form'          => $newSlideshowForm->createView(),
            'images'        => $images
        ));

        if ('POST' == $request->getMethod()) {
            $slideshowChoice = $request->get('slideshowchoice');

            if (isset($slideshowChoice['slideshows']) && !empty($images)) {
                foreach ($images as $image) {
                    foreach ($slideshowChoice['slideshows'] as $slideshow) {
                        $slideshow = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($slideshow);
                        // check for update access
                        if (false === $this->get('security.context')->isGranted('EDIT', $slideshow))
                        {
                            throw new AccessDeniedException();
                        }
                        $newImage = clone $image;
                        $newImage->setFromCatalog($em->find('BerkmanSlideshowBundle:Catalog', $image->getFromCatalog()->getId()));
                        $slide = new Slide($newImage);
                        $slideshow->addSlide($slide);
                        $slideshow->setUpdated(new \DateTime('now'));

                        $em->persist($slideshow);
                        $flashMessage = count($images) . ' slides added to slideshow "' . $slideshow->getName() . '"';
                        $this->get('session')->setFlash('notice', $flashMessage);
                    }
                }
                $request->getSession()->remove('finder');
                $em->flush();
                $response = $this->redirect($this->generateUrl('BerkmanSlideshowBundle_homepage'));
            }
        }

        return $response;
    }

    /**
     * Show the import screen
     */
    public function importAction()
    {
        $request = $this->getRequest();
        $masterForm = $this->createForm(new ImportType());
        $em = $this->getDoctrine()->getEntityManager();
        $images = array();
        $importForms = array();
        $finder = $this->getFinder();
        $catalogs = $em->getRepository('BerkmanSlideshowBundle:Catalog')->findAll();
        foreach ($catalogs as $catalog) {
            if ($catalog->hasCustomImporter()) {
                $importForms[$catalog->getId()] = $this->createForm(new ImportType())->createView();
            }
        }

        if ('POST' == $request->getMethod()) {
            $masterForm->bindRequest($request);
            if ($masterForm->isValid()) {
                $failed = array();
                $file = $masterForm['attachment']->getData();
                $file = $file->openFile();
                $file->setFlags(\SplFileObject::READ_CSV);
                foreach ($file as $row) {
                    if (isset($row[1])) {
                        $catalog = $row[0];
                        $args = array_slice($row, 1);
                        $catalog = $em->getRepository('BerkmanSlideshowBundle:Catalog')->find($catalog);
                        try {
                            $image = $catalog->getFetcher()->importImage($args);
                            $imageId = $finder->addImage($image);
                            $finder->addSelectedImageResult($imageId);
                        } catch (\ErrorException $e) {
                            $failed[] = $row;
                        }
                    }
                }
                error_log(count($failed) . ' images failed to import: ' . print_r($failed, true));
                $this->setFinder($finder);

                return $this->redirect($this->generateUrl('slideshow_add_images'));
            }
        }

        return $this->render('BerkmanSlideshowBundle:Slideshow:import.html.twig', array(
            'master_form' => $masterForm->createView(),
            'import_forms' => $importForms,
            'catalogs' => $catalogs
        ));

    }

    /**
     * Displays the FAQ page
     *
     * @return Symfony Response
     */
    public function faqAction()
    {
        return $this->render('BerkmanSlideshowBundle:FAQ:show.html.twig');
    }

    /**
     * Create a form to delete a slide
     *
     * @param integer $id  Slideshow id
     * @return Symfony Response
     */
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
