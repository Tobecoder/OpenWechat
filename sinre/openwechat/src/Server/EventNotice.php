<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/7/28
 * Time: 10:35
 */
namespace OpenWechat\Server;

use OpenWechat\Core\AbstractAPI;
use OpenWechat\Core\Collection;
use Pimple\Container;

class EventNotice extends AbstractAPI
{
    protected $container;
    public function __construct(Container $pimple)
    {
        $this->container = $pimple;
    }

    /**
     * 授权通知统一处理接口
     * @return bool|\OpenWechat\Core\Collection
     */
    public function notice()
    {
        $result = '';
        $decryptMsg = '';
        $errCode = $this->container->wxcrypt->decryptMsg(
            $this->container->request->get('msg_signature'),
            $this->container->request->get('timestamp'),
            $this->container->request->get('nonce'),
            $this->container->request->getContent(false),
            $decryptMsg
        );
        if(!$errCode){//成功解密
            $this->container->xml->setXml($decryptMsg);
            $infoType = $this->container->xml->getValue('InfoType');
            $xmlTimestamp = $this->container->xml->getValue('CreateTime');
            if(method_exists($this, $infoType)){
                $result = $this->$infoType($infoType);
                $result['createTime'] = $xmlTimestamp;
            };
            $result = $result ?: true;
        }else{
            $result = false;
        }
        return $result;
    }
    /**
     * 处理通知推送的tikect，需要输出success终止
     * @param string $infoType 通知类型
     * @return Collection
     */
    protected function component_verify_ticket($infoType)
    {
        $cacheKey = $this->container['config']['ticketKey'] . $this->container['config']['appid'];
        $ComponentVerifyTicket = $this->container->xml->getValue('ComponentVerifyTicket');
        $this->container->cache->save($cacheKey, $ComponentVerifyTicket);
        return new Collection([
            'infoType' => $infoType,
            'echostr' => 'success',
        ]);
    }

    /**
     * 授权公众号取消授权通知
     * @param string $infoType 通知类型
     * @return \OpenWechat\Core\Collection
     */
    protected function unauthorized($infoType)
    {
        $authorizerAppid = $this->container->xml->getValue('AuthorizerAppid');
        return new Collection([
            'infoType' => $infoType,
            'authorizerAppid' => $authorizerAppid,
        ]);
    }

    /**
     * 授权公众号授权成功通知
     * @param $infoType
     * @return \OpenWechat\Core\Collection
     */
    protected function authorized($infoType)
    {
        $authCode = $this->container->xml->getValue('AuthorizationCode');

        $result = $this->container->auth->getAuthInfoAndToken($authCode);

        $result['infoType'] = $infoType;
        return new Collection($result);
    }

    /**
     * 授权更新通知，在接收到通知后，业务应该更新授权公众号的
     * @param $infoType
     * @return \OpenWechat\Core\Collection
     */
    protected function updateauthorized($infoType)
    {
        return $this->authorized($infoType);
    }
}