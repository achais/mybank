<?php

namespace Achais\MYBank\Foundation\ServiceProviders;

use Achais\MYBank\Trade;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TradeProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['trade'] = function ($pimple) {
            return new Trade\Trade($pimple['config']);
        };
    }
}