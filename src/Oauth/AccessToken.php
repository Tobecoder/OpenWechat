<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/4
 * Time: 17:32
 */
namespace OpenWechat\Oauth;
use OpenWechat\Core\AbstractAPI;
use Pimple\Container;

class AccessToken extends AbstractAPI
{
    const ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/component/refresh_token';

    protected $container;
    protected $accessTokenCacheKey = 'sinre.openwechat.oauth_access_token.';
    protected $refreshTokenCacheKey = 'sinre.openwechat.oauth_refresh_token.';
    public function __construct(Container $pimple)
    {
        $this->container = $pimple;
        $this->cache = $this->container->cache;
    }

    /**
     * 缓存access token
     * @param string $appid 公众号APPID
     * @param array|\OpenWechat\Core\Collection $data access token 信息
     */
    public function cacheToken($appid, $data)
    {
        $access_token = $data['access_token'];
        $expires_in = $data['expires_in'];
        $refresh_token = $data['refresh_token'];
        $this->getCacheHandler()->save($this->accessTokenCacheKey . $appid, $access_token, $expires_in - 1500);
        $this->getCacheHandler()->save($this->refreshTokenCacheKey . $appid, $refresh_token, 0);
    }

    public function getToken($appid, $forceRefresh = false)
    {
        $access_token = $this->getCacheHandler()->fetch($this->accessTokenCacheKey . $appid);
        if(!$access_token || $forceRefresh){
            $access_token = $this->getAccessToken();
        }
        return $access_token;
    }
    protected function getAccessToken($appid)
    {
        $params = [
            'appid' => $appid,
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->getCacheHandler()->fetch($this->refreshTokenCacheKey . $appid),
            'component_appid' => $this->container['config']['appid'],
            'component_access_token' => $this->container->access_token->getCacheToken()
        ];
        $token = $this->parseJSON('get', [self::ACCESS_TOKEN_URL, $params]);
        if(!$token['errcode']){
            $this->cacheToken($appid, $token);
        }
    }
}