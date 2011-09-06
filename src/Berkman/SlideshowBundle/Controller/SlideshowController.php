<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Berkman\SlideshowBundle\Entity\Slideshow;
use Berkman\SlideshowBundle\Entity\Slide;
use Berkman\SlideshowBundle\Entity\Finder;
use Berkman\SlideshowBundle\Form\SlideshowType;
use Berkman\SlideshowBundle\Form\SlideshowChoiceType;
use Berkman\SlideshowBundle\Form\ImportType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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

        $deleteForm = $this->createDeleteForm($id);
        $images = array();
        foreach ($entity->getSlides() as $slide) {
            $images[] = $slide->getImage();
        }

        return $this->render('BerkmanSlideshowBundle:Slideshow:show.html.twig', array(
            'entity'      => $entity,
            'images'      => $images,
            'delete_form' => $deleteForm->createView(),
        ));
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

        $slideshow = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($id);

        if (!$slideshow) {
            throw $this->createNotFoundException('Unable to find Slideshow.');
        }

        return $this->render('BerkmanSlideshowBundle:Slideshow:slideshow.html.twig', array(
            'slideshow'      => $slideshow,
        ));
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
                        $newImage->setFromRepo($em->find('BerkmanSlideshowBundle:Repo', $image->getFromRepo()->getId()));
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

        $entity = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slideshow entity.');
        }

		// check for edit access
		if (false === $this->get('security.context')->isGranted('EDIT', $entity))
		{
			throw new AccessDeniedException();
		}

        $editForm = $this->createForm(new SlideshowType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BerkmanSlideshowBundle:Slideshow:edit.html.twig', array(
            'entity'      => $entity,
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
				$slideRepo = $em->getRepository('BerkmanSlideshowBundle:Slide');
				$newSlideIds = $entity->getSlides()->map(function ($slide) { return $slide->getId(); })->toArray();
				$slideIdsToRemove = array_diff($oldSlideIds, $newSlideIds);

				if ($slideIdsToRemove) {
					$slidesToRemove = $slideRepo->findById($slideIdsToRemove);
					foreach ($slidesToRemove as $slide) {
						$em->remove($slide);
					}
				}

				foreach (explode(',', $request->get('slide_order')) as $position => $slideId) {
					$slide = $slideRepo->find($slideId);
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

        return $this->redirect($this->generateUrl('slideshow'));
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
                        $newImage->setFromRepo($em->find('BerkmanSlideshowBundle:Repo', $image->getFromRepo()->getId()));
						$slide = new Slide($newImage);
						$slideshow->addSlide($slide);

						$em->persist($slideshow);
						$flashMessage = count($images) . ' slides added to slideshow "' . $slideshow->getName() . '"';
						$this->get('session')->setFlash('notice', $flashMessage);
					}
				}
                $request->getSession()->remove('finder');
				$em->flush();
				$response = $this->redirect($this->generateUrl('slideshow'));
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
        $form = $this->createForm(new ImportType());
		$em = $this->getDoctrine()->getEntityManager();
        $images = array();
        $finder = $this->getFinder();

        if ('POST' == $request->getMethod()) {
            $form->bindRequest($request);
            if ($form->isValid()) {
                $file = $form['attachment']->getData();
                $file = $file->openFile();
                $file->setFlags(\SplFileObject::READ_CSV);
                foreach ($file as $row) {
                    if (isset($row[1])) {
                        list($repo, $url) = $row;
                        $repo = $em->getRepository('BerkmanSlideshowBundle:Repo')->find($repo);
                        $imageId = $finder->addImage($repo->getFetcher()->importImage($url));
                        $finder->addSelectedImageResult($imageId);
                    }
                }
                $this->setFinder($finder);

                return $this->forward('BerkmanSlideshowBundle:Slideshow:addImages');
            }
        }

        return $this->render('BerkmanSlideshowBundle:Slideshow:import.html.twig', array(
            'import_form' => $form->createView()
        ));

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
