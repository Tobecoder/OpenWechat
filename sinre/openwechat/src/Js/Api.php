<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/9
 * Time: 11:17
 */
namespace OpenWechat\Js;
use Doctrine\Common\Cache\Cache;
use OpenWechat\Core\AbstractAPI;
use OpenWechat\Core\Helper\StringHelper;

class Api extends AbstractAPI
{
    const JSAPI_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?';
    protected $auth;
    protected $cacheKey = 'sinre.openwechat.jsapi_ticket.';
    public function __construct($auth, Cache $cache)
    {
        $this->auth = $auth;
    }

    public function getCacheJsTicket($appid, $forceRefresh = false)
    {
        $ticket = $this->getCacheHandler()->fetch($this->cacheKey . $appid);
        if(!$ticket || $forceRefresh){
            $ticket = $this->getJsTicket($appid);
        }
        return $ticket;
    }
    protected function getJsTicket($appid)
    {
        $params = [
            'access_token' => $this->auth->getAuthorizerToken($appid),
            'type' => 'jsapi'
        ];
        $result = $this->parseJSON('get', [self::JSAPI_TICKET, $params]);

        $this->getCacheHandler()->save($this->cacheKey . $appid, $result['ticket'], $result['expires_in'] - 500);

        return $result['ticket'];
    }

    protected function signature($ticket, $nonce, $timestamp, $url)
    {
        return sha1("jsapi_ticket={$ticket}&noncestr={$nonce}&timestamp={$timestamp}&url={$url}");
    }

    protected function fixUrl($url)
    {
        $position = strpos($url, '#');
        if($position){
            $url = substr($url, 0, $position);
        }
        return $url;
    }

    public function getJsSign($appid, $url, $noncestr = null, $timestamp = null)
    {
        $jsapi_ticket = $this->getCacheJsTicket($appid);
        $noncestr = $noncestr ?: StringHelper::quickRandom(10);
        $timestamp = $timestamp ?: time();
        $url = $this->fixUrl($url);
        return  [
            'appid' => $appid,
            'nonceStr' => $noncestr,
            'timestamp' => $timestamp,
            'url' => $url,
            'signature' => $this->signature($jsapi_ticket, $noncestr, $timestamp, $url)
        ];
    }

}