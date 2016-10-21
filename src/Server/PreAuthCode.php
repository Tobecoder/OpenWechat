<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/27
 * Time: 9:59
 */
namespace OpenWechat\Server;

use Doctrine\Common\Cache\Cache;
use OpenWechat\Core\AbstractAPI;

class PreAuthCode extends AbstractAPI
{
    private $appid;
    private $codePrefix;
    const PRE_AUTH_CODE = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=';

    public function __construct($appid, Cache $cache)
    {
        $this->appid = $appid;
        $this->cache = $cache;
        $this->codePrefix = 'sinre.openwechat.pre_auth_code.';
    }

    /**
     * 获取预授权码
     * @param string $access_token 第三方平台component_access_token
     * @param bool $forceRefresh 是否强制刷新
     * @return false|mixed|null
     */
    public function getCode($access_token, $forceRefresh = false)
    {
        $cacheKey = $this->codePrefix . $this->appid;
        $preAuthCode = $this->getCacheHandler()->fetch($cacheKey);
        if(!$preAuthCode || $forceRefresh){
            $preAuthCode = $this->getPreAuthCode($access_token);
        }
        return $preAuthCode;
    }

    /**
     * 获取预授权码
     * @param string $access_token 第三方平台component_access_token
     * @return mixed|null
     */
    protected function getPreAuthCode($access_token)
    {
        $params = [
            'component_appid' => $this->appid
        ];
        $codeinfo = $this->parseJSON('post', [self::PRE_AUTH_CODE . $access_token, json_encode($params)]);

        $cacheKey = $this->codePrefix . $this->appid;
        $this->getCacheHandler()->save($cacheKey, $codeinfo['pre_auth_code'], $codeinfo['expires_in'] - 300);

        return $codeinfo['pre_auth_code'];
    }
}