<?php

namespace Achais\MYBank\Foundation\ServiceProviders;

use Achais\MYBank\User;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UserProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['user'] = function ($pimple) {
            return new User\User($pimple['config']);
        };
    }
}