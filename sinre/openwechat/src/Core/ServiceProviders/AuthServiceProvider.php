<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/27
 * Time: 11:32
 */
namespace OpenWechat\Core\ServiceProviders;

use OpenWechat\Auth\AccessToken;
use OpenWechat\Auth\Auth;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AuthServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['auth'] = function() use ($pimple){
            return new Auth($pimple);
        };

        $pimple['authorizer_access_token'] = function() use ($pimple){
            return new AccessToken($pimple['config']['appid'], $pimple['cache']);
        };
    }

}