<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/8
 * Time: 11:32
 */
namespace OpenWechat\Core\ServiceProviders;

use OpenWechat\Message\Message;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MessageServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['message'] = function() use ($pimple){
            return new Message($pimple['wxcrypt'], $pimple['request'], $pimple['xml']);
        };
    }

}