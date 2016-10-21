<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/4
 * Time: 15:37
 */
namespace OpenWechat\Core\ServiceProviders;

use OpenWechat\Oauth\AccessToken;
use OpenWechat\Oauth\Oauth;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OauthServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['oauth'] = function() use ($pimple){
            return new Oauth($pimple['config']['appid'], $pimple['access_token'], $pimple['oauth_access_token']);
        };

        $pimple['oauth_access_token'] = function() use ($pimple){
            return new AccessToken($pimple);
        };
    }

}