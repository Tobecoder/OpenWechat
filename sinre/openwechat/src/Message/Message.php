<?php
/**
 * Created by PhpStorm.
 * User: sinre05
 * Date: 2016/8/8
 * Time: 11:37
 */
namespace OpenWechat\Message;
use OpenWechat\Core\Collection;
use OpenWechat\Core\Exceptions\FaultException;
use OpenWechat\Core\Exceptions\InvalidArgumentException;
use OpenWechat\Core\Helper\XmlHelper;
use OpenWechat\Message\Type\AbstractMessage;
use OpenWechat\Message\Type\Text;
use Symfony\Component\HttpFoundation\Request;

class Message extends AbstractMessage
{
    const SUCCESS_EMPTY_RESPONSE = 'success';

    const TEXT_MSG = 2;
    const IMAGE_MSG = 4;
    const VOICE_MSG = 8;
    const VIDEO_MSG = 16;
    const SHORT_VIDEO_MSG = 32;
    const LOCATION_MSG = 64;
    const LINK_MSG = 128;
    const EVENT_MSG = 1048576;
    const ALL_MSG = 1048830;

    protected $wxcrypt;
    protected $request;
    protected $xml;
    protected $postxml;

    protected $messageHandler;
    protected $messageFilter;
    /**
     * @var array
     */
    protected $messageTypeMapping = [
        'text' => 2,
        'image' => 4,
        'voice' => 8,
        'video' => 16,
        'shortvideo' => 32,
        'location' => 64,
        'link' => 128,
        'event' => 1048576,
    ];

    public function __construct($wxcrypt, Request $request, $xml)
    {
        $this->wxcrypt = $wxcrypt;
        $this->request = $request;
        $this->xml = $xml;
    }

    public function getMessage()
    {
        $postStr = $this->request->getContent(false);
        if ($this->isSafeMode()) { //aes加密
            $errcode = $this->wxcrypt->decryptMsg($this->request->get('msg_signature'), $this->request->get('timestamp'), $this->request->get('nonce'), $postStr, $this->postxml);
            if($errcode != 0){
                throw new FaultException('decrypt error!');
            }
        } else {
            $this->postxml = $postStr;
        }
        $this->xml->setXml($this->postxml);
        return $this->xml->getFrameUnRecursive('xml');
    }

    protected function handleMessage($message)
    {
        $handler = $this->messageHandler;

        if (!is_callable($handler)) {
            return;
        }

        $message = new Collection($message);

        $type = $this->messageTypeMapping[$message->get('MsgType')];

        $response = null;
        if ($this->messageFilter & $type) {
            $response = call_user_func_array($handler, [$message]);
        }

        return $response;
    }

    protected function isMessage($message)
    {
        if (is_array($message)) {
            foreach ($message as $element) {
                if (!is_subclass_of($element, AbstractMessage::class)) {
                    return false;
                }
            }

            return true;
        }

        return is_subclass_of($message, AbstractMessage::class);
    }

    /**
     * Build reply XML.
     *
     * @param string          $to
     * @param string          $from
     * @param AbstractMessage $message
     *
     * @return string
     */
    protected function buildReply($to, $from, $message)
    {
        $base = [
            'ToUserName' => $to,
            'FromUserName' => $from,
            'CreateTime' => time(),
            'MsgType' => is_array($message) ? current($message)->getMsgType() : $message->getMsgType(),
        ];

        $transformer = new Transformer();

        return XmlHelper::build(array_merge($base, $transformer->transform($message)));
    }

    protected function buildResponse($to, $from, $message)
    {
//        if(is_null($message)){
//            return '';
//        }

        if (empty($message) || $message === self::SUCCESS_EMPTY_RESPONSE) {
            return self::SUCCESS_EMPTY_RESPONSE;
        }

//        if ($message instanceof RawMessage) {
//            return $message->get('content', self::SUCCESS_EMPTY_RESPONSE);
//        }

        if (is_string($message)) {
            $message = new Text(['content' => $message]);
        }

        if (!$this->isMessage($message)) {
            throw new InvalidArgumentException("Invalid Message type .'{gettype($message)}'");
        }

        $response = $this->buildReply($to, $from, $message);

        if ($this->isSafeMode()) {
            $this->wxcrypt->encryptMsg(
                $response,
                $this->request->get('timestamp'),
                $this->request->get('nonce'),
                $response
            );
        }

        return $response;
    }

    /**
     * @param callable $callable
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setMessageHandler(callable $callable, $option = self::ALL_MSG)
    {
        if (!is_callable($callable)) {
            throw new InvalidArgumentException('Argument #2 is not callable.');
        }
        $this->messageHandler = $callable;
        $this->messageFilter = $option;
        return $this;
    }
    public function reply()
    {
        if ($echostr = $this->request->get('echostr')) {
            $errcode = $this->wxcrypt->VerifyURL($this->request->get('msg_signature'), $this->request->get('timestamp'), $this->request->get('nonce'), $echostr, $this->postxml);
            if($errcode == 0){
                return $this->postxml;
            }else{
                throw new FaultException('decrypt error!');
            }
        }
        $message = $this->getMessage();
        $response = $this->handleMessage($message);

        $result = [
            'to' => $message['FromUserName'],
            'from' => $message['ToUserName'],
            'response' => $response,
        ];

        $response = $this->buildResponse($result['to'], $result['from'], $result['response']);

        return $response;
    }

    /**
     * Check the request message safe mode.
     *
     * @return bool
     */
    private function isSafeMode()
    {
        return $this->request->get('encrypt_type') && $this->request->get('encrypt_type') === 'aes';
    }

}