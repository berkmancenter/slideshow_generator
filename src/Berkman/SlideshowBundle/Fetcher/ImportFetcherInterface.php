<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface ImportFetcherInterface {
	public function getImagesFromImport(\SPLFileObject $file);
	public function getImportInstructions();
}
