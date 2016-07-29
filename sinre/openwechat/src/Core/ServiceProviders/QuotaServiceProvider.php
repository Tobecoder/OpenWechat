<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/29
 * Time: 15:41
 */
namespace OpenWechat\Core\ServiceProviders;
use OpenWechat\Quota\Quota;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class QuotaServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['quota'] = function() use ($pimple){
            return new Quota($pimple);
        };
    }

}