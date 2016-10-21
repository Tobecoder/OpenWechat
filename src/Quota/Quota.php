<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/29
 * Time: 15:43
 */
namespace OpenWechat\Quota;
use OpenWechat\Core\AbstractAPI;
use Pimple\Container;

class Quota extends AbstractAPI
{
    protected $container;

    const CLEAR_QUOTA_URL = 'https://api.weixin.qq.com/cgi-bin/clear_quota?access_token=';
    const CLEAR_ALL_QUOTA_URL = 'https://api.weixin.qq.com/cgi-bin/component/clear_quota?component_access_token=';

    public function __construct(Container $pimple)
    {
        $this->container = $pimple;
    }

    /**
     * 第三方代公众号调用对公众号的所有API调用（包括第三方代公众号调用）次数进行清零
     * @param string $authorizer_appid 授权方appid
     * @return \OpenWechat\Core\Collection
     */
    public function clearQuota($authorizer_appid)
    {
        $component_access_token = $this->container->access_token->getCacheToken();
        $access_token = $this->container->authorizer_access_token->getToken($authorizer_appid, $component_access_token);

        $params = [
            'appid' => $authorizer_appid
        ];

        return $this->parseJSON('post', [self::CLEAR_QUOTA_URL . $access_token, json_encode($params)]);
    }
    /**
     * 第三方平台对其所有API调用次数清零（只与第三方平台相关，与公众号无关）
     * @return \OpenWechat\Core\Collection
     */
    public function clearAllQuota()
    {
        $access_token = $this->container->access_token->getCacheToken();
        $params = [
            'component_appid' => $this->container['config']['appid'],
        ];
        $errInfo = $this->parseJSON('post', [self::CLEAR_ALL_QUOTA_URL . $access_token, json_encode($params)]);
        return $errInfo;
    }
}