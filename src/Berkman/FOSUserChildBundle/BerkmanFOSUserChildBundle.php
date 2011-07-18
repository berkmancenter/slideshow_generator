<?php

namespace Berkman\FOSUserChildBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BerkmanFOSUserChildBundle extends Bundle
{
	public function getParent()
	{
		return 'FOSUserBundle';
	}
}
