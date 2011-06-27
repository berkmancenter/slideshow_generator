<?php

namespace Berkman\SlideshowBundle\Parser;

use Berkman\SlideshowBundle\Entity\Repo;

class VIA extends Repo implements ParserInterface {

	protected $input;

	public function __construct($input) {
		$this->input = $input;
	}

	public function getId1() {
	}

	public function getId2() {
	}

	public function getId3() {
	}
}
