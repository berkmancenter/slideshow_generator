<?php

namespace Berkman\SlideshowBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Berkman\SlideshowBundle\Entity\Repo;

class LoadTestRepos implements FixtureInterface
{
	public function load($manager)
	{
		$viaRepo = new Repo();
		$viaRepo->setId('VIA');
		$viaRepo->setName('Visual Information Access (VIA)');
		$viaRepo->setSearchUrlPattern('http://webservices.lib.harvard.edu/rest/hollis/search/dc/?curpage={page}&q=material-id:matPhoto+{keyword}');
		$viaRepo->setRecordUrlPattern('http://via.lib.harvard.edu:80/via/deliver/deepLinkItem?recordId={id-2}&componentId={id-3}'); 
		$viaRepo->setImageUrlPattern('http://nrs.harvard.edu/urn-3:{id-3}');
		$viaRepo->setMetadataUrlPattern('http://webservices.lib.harvard.edu/rest/marc/hollis/{id-2}');
		$viaRepo->setThumbnailUrlPattern('http://nrs.harvard.edu/urn-3:{id-4}');

		$manager->persist($viaRepo);
		$manager->flush();
	}
}
