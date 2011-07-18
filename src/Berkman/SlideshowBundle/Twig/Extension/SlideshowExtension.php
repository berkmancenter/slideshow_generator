<?php

namespace Berkman\SlideshowBundle\Twig\Extension;

class SlideshowExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'slideshow';
    }

	public function getFunctions()
    {
        return array('image_label' => new \Twig_Function_Method($this, 'imageLabel'));
    }

	public function imageLabel($child) {
		//return print_r($child, true);
		return $child->get('attr');

	}
}
