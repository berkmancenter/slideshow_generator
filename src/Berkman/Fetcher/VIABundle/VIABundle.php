<?php

namespace Berkman\Fetcher\VIABundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class VIABundle extends Bundle
{
    public function getParent()
    {
        return 'BerkmanFetcherBundle';
    }
}
