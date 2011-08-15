<?php

namespace Berkman\SlideshowBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Berkman\SlideshowBundle\Entity\Repo;

class LoadTestRepos implements FixtureInterface
{
	public function load($manager)
	{
		$via = new Repo();
		$via->setId('VIA');
		$via->setName('Visual Information Access (VIA)');

        $oasis = new Repo();
        $oasis->setId('OASIS');
        $oasis->setName('Online Archival Search Information System (OASIS)');

		$manager->persist($via);
        $manager->persist($oasis);
		$manager->flush();
	}
}
