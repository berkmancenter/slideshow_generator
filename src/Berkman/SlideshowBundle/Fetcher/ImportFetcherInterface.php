<?php

namespace Berkman\SlideshowBundle\Fetcher;

use Berkman\SlideshowBundle\Entity;

interface ImportFetcherInterface {
	public function getImagesFromImport(Entity\Batch $batch);
	public function getImportInstructions();
}
