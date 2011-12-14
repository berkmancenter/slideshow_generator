<?php
namespace Berkman\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
