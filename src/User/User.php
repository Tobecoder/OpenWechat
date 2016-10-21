<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/8
 * Time: 17:44
 */
namespace OpenWechat\User;

use OpenWechat\Core\AbstractAPI;

class User extends AbstractAPI
{
    const USER_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/user/info?';
    protected $auth;
    public function __construct($auth)
    {
        $this->auth = $auth;
    }

    public function getInfo($appid, $openid, $lang = 'zh_CN')
    {
        $access_token = $this->auth->getAuthorizerToken($appid);
        $params = [
            'access_token' => $access_token,
            'openid' => $openid,
            'lang' => $lang
        ];
        $userInfo = $this->parseJSON('get', [self::USER_INFO_URL, $params]);
        return $userInfo;
    }

}