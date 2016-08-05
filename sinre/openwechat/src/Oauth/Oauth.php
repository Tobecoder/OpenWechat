<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/4
 * Time: 15:38
 */
namespace OpenWechat\Oauth;
use OpenWechat\Core\AbstractAPI;
use OpenWechat\Core\Collection;

class Oauth extends AbstractAPI
{
    const GET_CODE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
    const GET_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/component/access_token';
    const GET_USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo?';

    protected $appid;
    protected $access_token;
    protected $oauth_access_token;
    public function __construct($appid, $access_token, $oauth_access_token)
    {
        $this->appid = $appid;
        $this->access_token = $access_token;
        $this->oauth_access_token = $oauth_access_token;
    }

    /**
     * 获取授权跳转地址
     * @param string $appid 公众号APPID
     * @param string $redirect_uri 回调地址
     * @param string $scope 授权作用域，拥有多个作用域用逗号（,）分隔。一般而言，已微信认证的服务号拥有snsapi_base和snsapi_userinfo
     * @param string $state 重定向后会带上state参数，开发者可以填写任意参数值，最多128字节
     * @return string
     */
    public function getOauthRedirect($appid, $redirect_uri, $scope = 'snsapi_userinfo', $state = '')
    {
        $params = [
            'appid' => $appid,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'component_appid' => $this->appid
        ];
        return self::GET_CODE_URL . http_build_query($params) . '#wechat_redirect';
    }

    /**
     * @param string $appid 公众号App id
     * @param string $code 授权码
     * @return bool|\OpenWechat\Core\Collection
     */
    public function getOauthAccessToken($appid, $code)
    {
        $params = [
            'appid' => $appid,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'component_appid' => $this->appid,
            'component_access_token' => $this->access_token->getCacheToken()
        ];
        $result = $this->parseJSON('get', [self::GET_TOKEN_URL, $params]);
        if(!$result['errcode']){
            $data = [
                'access_token' => $result['access_token'],
                'openid' => $result['openid'],
                'scope' => $result['scope'],
            ];
            $this->oauth_access_token->cacheToken($appid, $result);
        }
        return !$result['errcode'] ? new Collection($data) : false;
    }

    /**
     * @param $access_token
     * @param $openid
     * @param string $lang
     * @return bool|Collection
     */
    public function getOauthUserinfo($access_token, $openid, $lang = 'zh_CN')
    {
        $params = [
            'access_token' => $access_token,
            'openid' => $openid,
            'lang' => $lang
        ];
        $result = $this->parseJSON('get', [self::GET_USERINFO_URL, $params]);
        return !$result['errcode'] ? $result : false;
    }
}