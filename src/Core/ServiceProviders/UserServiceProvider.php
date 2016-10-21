<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/8
 * Time: 17:46
 */
namespace OpenWechat\Core\ServiceProviders;
use OpenWechat\User\User;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class UserServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['user'] = function() use ($pimple){
            return new User($pimple['auth']);
        };
    }

}