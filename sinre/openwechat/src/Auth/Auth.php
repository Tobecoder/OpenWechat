<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/27
 * Time: 11:37
 */
namespace OpenWechat\Auth;

use OpenWechat\Core\AbstractAPI;
use Pimple\Container;

class Auth extends AbstractAPI
{
    protected $container;

    const AUTH_URL = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?';
    const AUTH_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=';

    public function __construct(Container $pimple)
    {
        $this->container = $pimple;
    }

    /**
     * 获取预授权码
     * @return mixed
     */
    public function getPreAuthCode()
    {
        $token = $this->container->access_token->getCacheToken();
        return $this->container->autocode->getCode($token);
    }

    /**
     * 构造微信公众号授权入口
     * @param $redirect_uri 回调地址
     * @return string
     */
    public function buildAuthUrl($redirect_uri)
    {
        $authcode = $this->getPreAuthCode();
        $params = [
            'component_appid' => $this->container['config']['appid'],
            'pre_auth_code' => $authcode,
            'redirect_uri' => $redirect_uri
        ];
        return self::AUTH_URL . http_build_query($params);
    }

    /**
     * 根据回调获取的授权码(auth_code)换取公众号的接口调用凭据和授权信息
     * @param string $authCode 授权码
     * @return array 请注意：func_info该字段的返回不会考虑公众号是否具备该权限集的权限（因为可能部分具备），请根据公众号的帐号类型和认证情况，来判断公众号的接口权限。
     */
    public function getAuthInfoAndToken($authCode)
    {
        $access_token = $this->container->access_token->getCacheToken();
        $params = [
            'component_appid' => $this->container['config']['appid'],
            'authorization_code' => $authCode,
        ];
        $authInfo = $this->parseJSON('post', [self::AUTH_INFO_URL . $access_token, json_encode($params)]);
        $authorization_info = $authInfo['authorization_info'];

        $this->container->authorizer_access_token->cacheToken($authorization_info['authorizer_appid'], $authorization_info['authorizer_access_token'], $authorization_info['expires_in'], $authorization_info['authorizer_refresh_token']);

        return $authorization_info;
    }

    /**
     * 刷新授权公众号的接口调用凭据Token
     * @param string $authorizer_appid 授权方appid
     * @param bool $forceRefresh 是否强制刷新token
     * @return mixed
     */
    public function getAuthorizerToken($authorizer_appid, $forceRefresh = false)
    {
        $access_token = $this->container->access_token->getCacheToken();
        return $this->container->authorizer_access_token->getToken($authorizer_appid, $access_token, $forceRefresh);
    }

}