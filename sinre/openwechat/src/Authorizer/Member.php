<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/27
 * Time: 16:57
 */
namespace OpenWechat\Authorizer;
use OpenWechat\Core\AbstractAPI;
use OpenWechat\Server\AccessToken;

class Member extends AbstractAPI
{
    protected $appid;
    protected $access_token;

    const BASE_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=';
    const GET_SETTING_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option?component_access_token=';
    const SET_SETTING_INFO_URL = 'https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option?component_access_token=';

    public function __construct($appid, AccessToken $access_token)
    {
        $this->appid = $appid;
        $this->access_token = $access_token;
    }

    /**
     * 获取授权方的公众号帐号基本信息
     * @param string $authorizer_appid 授权方appid
     */
    public function getBaseInfo($authorizer_appid)
    {
        $access_token = $this->access_token->getCacheToken();

        $params = [
            'component_appid' => $this->appid,
            'authorizer_appid' => $authorizer_appid,
        ];

        $info = $this->parseJSON('post', [self::BASE_INFO_URL . $access_token, json_encode($params)]);

        return $info;
    }

    /**
     * 获取授权方的选项设置信息
     * @param string $authorizer_appid 授权方appid
     * @param string $option_name 选项名称，location_report(地理位置上报选项)，voice_recognize（语音识别开关选项），customer_service（多客服开关选项）
     * 对应的值含义：
     * location_report(地理位置上报选项)	0	无上报    1	进入会话时上报    2	每5s上报
     * voice_recognize（语音识别开关选项）	0	关闭语音识别    1	开启语音识别
     * customer_service（多客服开关选项）	0	关闭多客服    1	开启多客服
     */
    public function getSettingInfo($authorizer_appid, $option_name)
    {
        $access_token = $this->access_token->getCacheToken();

        $params = [
            'component_appid' => $this->appid,
            'authorizer_appid' => $authorizer_appid,
            'option_name' => $option_name
        ];

        $optionSetting = $this->parseJSON('post', [self::GET_SETTING_INFO_URL . $access_token, json_encode($params)]);

        return $optionSetting;
    }

    /**
     * 设置授权方的选项设置信息
     * @param string $authorizer_appid 授权方appid
     * @param string $option_name 选项名称，location_report(地理位置上报选项)，voice_recognize（语音识别开关选项），customer_service（多客服开关选项）
     * @param int $option_value 选项值
     * 对应的值含义：
     * location_report(地理位置上报选项)	0	无上报    1	进入会话时上报    2	每5s上报
     * voice_recognize（语音识别开关选项）	0	关闭语音识别    1	开启语音识别
     * customer_service（多客服开关选项）	0	关闭多客服    1	开启多客服
     */
    public function setSettingInfo($authorizer_appid, $option_name, $option_value)
    {
        $access_token = $this->access_token->getCacheToken();

        $params = [
            'component_appid' => $this->appid,
            'authorizer_appid' => $authorizer_appid,
            'option_name' => $option_name,
            'option_value' => $option_value,
        ];

        $errInfo = $this->parseJSON('post', [self::SET_SETTING_INFO_URL . $access_token, json_encode($params)]);

        return $errInfo;
    }
}