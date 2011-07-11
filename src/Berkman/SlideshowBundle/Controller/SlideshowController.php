<?php

namespace Berkman\SlideshowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Berkman\SlideshowBundle\Entity\Slideshow;
use Berkman\SlideshowBundle\Entity\Image;
use Berkman\SlideshowBundle\Entity\Slide;
use Berkman\SlideshowBundle\Form\SlideshowType;
use Berkman\SlideshowBundle\Form\FindShow;
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
     * Lists all Slideshow entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->findAll();

        return $this->render('BerkmanSlideshowBundle:Slideshow:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Lists all Slideshow entities in a nicer layout
     *
     */
    public function browseAction()
    {
        $em = $this->getDoctrine()->getEntityManager();

        $entities = $em->getRepository('BerkmanSlideshowBundle:Slideshow')->findAll();

        return $this->render('BerkmanSlideshowBundle:Slideshow:browse.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Slideshow entity.
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
    public function createAction($slides = array())
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
				foreach ($slides as $slide) {
					$slideshow->addSlide($slide);
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

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Slideshow entity.');
        }

        $editForm   = $this->createForm(new SlideshowType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        if ('POST' === $request->getMethod()) {
            $editForm->bindRequest($request);

            if ($editForm->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($entity);
                $em->flush();

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
	 */
	public function addImageAction()
	{
		$request = $this->getRequest();
		$images = array();

		if ('POST' == $request->getMethod()) {
			$images = $request->get('findresults');
			$images = $images['find']['images'];
			$imageObjects = array();
			$em = $this->getDoctrine()->getEntityManager();

			foreach ($images as $image) {
				$image = unserialize(base64_decode($image));
				$fromRepo = $em->getRepository('BerkmanSlideshowBundle:Repo')->find($image['fromRepo']);
				$image = new Image($fromRepo, $image['id1'], $image['id2'], $image['id3'], $image['id4']);
				$em->persist($image);
				$imageObjects[] = $image;
			}
			$em->flush();

			$slideshow = new Slideshow();
			foreach ($imageObjects as $image) {
				$slide = new Slide();
				$slide->setImage($image);
				$slide->setSlideshow($slideshow);
				$slideshow->addSlides($slide);
				$em->persist($slide);
			}
			$em->persist($slideshow);
			$em->flush();
		}

		return $this->redirect($this->generateUrl('slideshow'));
	}

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
