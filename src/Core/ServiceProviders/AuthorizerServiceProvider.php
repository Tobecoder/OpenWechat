<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/27
 * Time: 17:41
 */

namespace OpenWechat\Core\ServiceProviders;

use OpenWechat\Authorizer\Member;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AuthorizerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['member'] = function() use ($pimple){
            return new Member($pimple['config']['appid'], $pimple['access_token']);
        };
    }

}