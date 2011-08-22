<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Berkman\SlideshowBundle\Entity\Slideshow;
use Berkman\SlideshowBundle\Entity\Image;
use Berkman\SlideshowBundle\Entity\Slide;
use Berkman\SlideshowBundle\Form\SlideshowType;
use Berkman\SlideshowBundle\Form\SlideshowChoiceType;
use Berkman\SlideshowBundle\Form\FinderShow;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * Slideshow controller.
 *
 */
class SlideshowController extends Controller
{
    /**
     * Finders and displays a Slideshow entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entity = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slideshow entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('BerkmanSlideshowBundle:Slideshow:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

	/**
	 * Displays a slideshow
	 *
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
                $finder = $em->find('BerkmanSlideshowBundle:Finder', $this->getRequest()->getSession()->get('finder_id'));

                if ($finder) {
                    // TODO: Decide what to do with collections here
                    $images = $finder->getSelectedImageResults();
                    $finder->setSelectedImageResults(array());
                    $em->persist($finder);
                    $em->flush();
					foreach ($images as $image) {
                        $newImage = clone $image;
						$slide = new Slide($newImage);
						$slideshow->addSlide($slide);
					}
                    $em->remove($finder);
					$request->getSession()->remove('finder_id');
				}
                $em->persist($slideshow);
                $em->flush();

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

                $em->persist($entity);
                $em->flush();

				$request->getSession()->setFlash('notice', 'Slideshow successfully updated');

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

				// check for update access
				if (false === $this->get('security.context')->isGranted('DELETE', $entity))
				{
					throw new AccessDeniedException();
				}

				foreach ($slides as $slide) {
					$em->remove($slide);
				}
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
     * 
     * Images always come from the finder object
	 *
	 */
	public function addImagesAction()
	{
		$slideshow = new Slideshow();
		$request   = $this->getRequest();
		$em        = $this->getDoctrine()->getEntityManager();
        $finder    = $em->find('BerkmanSlideshowBundle:Finder', $request->getSession()->get('finder_id'));
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
						$slide = new Slide($newImage);
						$slideshow->addSlide($slide);

						$em->persist($slideshow);
						$flashMessage = count($images) . ' slides added to ' . $slideshow->getName();
						$this->get('session')->setFlash('notice', $flashMessage);
					}
				}
                $request->getSession()->remove('finder_id');
                $em->remove($finder);
				$em->flush();
				$response = $this->redirect($this->generateUrl('slideshow'));
				$response->headers->clearCookie('images');
			}
		}

        return $response;
	}

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
