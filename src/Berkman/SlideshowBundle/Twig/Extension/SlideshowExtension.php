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
        return array('image_label' => new \Twig_Function_Method($this, 'imageLabel', array('is_safe' => array('html'))));
    }

	public function imageLabel($child) {
		return '<label for="'.$child->get('id').'"><img src="'.$child->get('label').'" /></label>';
	}
}
