<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/9
 * Time: 11:14
 */
namespace OpenWechat\Core\ServiceProviders;
use OpenWechat\Js\Api;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class JsApiServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['jsapi'] = function() use ($pimple){
            return new Api($pimple['auth'], $pimple['cache']);
        };
    }

}