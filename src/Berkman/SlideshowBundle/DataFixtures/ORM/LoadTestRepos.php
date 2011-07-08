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

		$manager->persist($viaRepo);
		$manager->flush();
	}
}
