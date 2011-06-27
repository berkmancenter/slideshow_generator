<?php 

namespace Berkman\SlideshowBundle\Model;

class Search {

	protected $keyword;
	protected $repos;

	public function __construct($keyword, array $repos) {
		$this->keyword = $keyword;
		$this->repos = $repos;
	}

	public function execute() {
		foreach ($repo in $repos) {
		}
	}
}
