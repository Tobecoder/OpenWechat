<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/26
 * Time: 12:18
 */
namespace OpenWechat\Server;

use Doctrine\Common\Cache\Cache;
use OpenWechat\Core\AbstractAPI;

class AccessToken extends AbstractAPI
{
    const COMPONENT_ACCESS_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';

    protected $appId;
    protected $secret;
    protected $cache;
    protected $ticketCacheKey;
    protected $tokenPrefix;
    /**
     * Constructor.
     *
     * @param string                       $appId
     * @param string                       $secret
     * @param \Doctrine\Common\Cache\Cache $cache
     * @param string                       $cacheKey
     */
    public function __construct($appId, $secret, Cache $cache = null, $cacheKey = null)
    {
        $this->appId = $appId;
        $this->secret = $secret;
        $this->cache = $cache;
        $this->ticketCacheKey = $cacheKey;
        $this->tokenPrefix = 'sinre.openwechat.access_token.';
    }

    /**
     * 获取第三方平台component_access_token
     * @return mixed|null
     */
    protected function getAccessToken()
    {
        $params = [
            'component_appid' => $this->appId,
            'component_appsecret' => $this->secret,
            'component_verify_ticket' => $this->getCacheHandler()->fetch($this->ticketCacheKey)
        ];
        $access_token = $this->parseJSON('post', [self::COMPONENT_ACCESS_TOKEN, json_encode($params)]);

        $cacheKey = $this->tokenPrefix . $this->appId;
        $this->getCacheHandler()->save($cacheKey, $access_token['component_access_token'], $access_token['expires_in'] - 1500);

        return $access_token['component_access_token'];
    }

    /**
     * 获取第三方平台component_access_token
     * @param bool $forceRefresh 强制刷新
     * @return false|mixed|null
     */
    public function getCacheToken($forceRefresh = false)
    {
        $cacheKey = $this->tokenPrefix . $this->appId;
        $access_token = $this->getCacheHandler()->fetch($cacheKey);
        if(!$access_token || $forceRefresh){
            $access_token = $this->getAccessToken();
        }
        return $access_token;
    }

}