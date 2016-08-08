<?php
/**
 * Created by PhpStorm.
 * User: sinre05
 * Date: 2016/8/8
 * Time: 11:37
 */
namespace OpenWechat\Message;
use OpenWechat\Core\AbstractAPI;
use Symfony\Component\HttpFoundation\Request;

class Message extends AbstractAPI
{
    protected $wxcrypt;
    protected $request;
    protected $xml;
    protected $postxml;
    public function __construct($wxcrypt, Request $request, $xml)
    {
        $this->wxcrypt = $wxcrypt;
        $this->request = $request;
        $this->xml = $xml;
    }

    protected function parseMessage($params)
    {
        if ($this->request->getRealMethod() == "POST") {
            $postStr = file_get_contents("php://input");
            if ($params['encrypt_type'] == 'aes') { //aes加密
                $errcode = $this->wxcrypt->decryptMsg($params['msg_signature'], $params['timestamp'], $params['nonce'], $postStr, $this->postxml);
                if($errcode != 0){
                    die('decrypt error!');
                }
            } else {
                $this->postxml = $postStr;
            }
        } elseif (isset($params["echostr"])) {
            $errcode = $this->wxcrypt->VerifyURL($params['msg_signature'], $params['timestamp'], $params['nonce'], $params["echostr"], $this->postxml);
            if($errcode == 0){
                echo $this->postxml;
                exit();
            }else{
                die('no access');
            }
        }
        return true;
    }

    public function receiveMessage($params)
    {
        $this->parseMessage($params);
        $this->xml->setXml($this->postxml);
//        \think\Log::write($this->xml->getFrameUnRecursive('xml'));
        return $this->xml->getFrameUnRecursive('xml');
    }
}