<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/11
 * Time: 11:47
 */
namespace OpenWechat\Message;
use OpenWechat\Message\Type\AbstractMessage;
use OpenWechat\Message\Type\Text;

class Transformer
{
    protected $to;
    protected $from;

    public function transform($message)
    {
        if(is_array($message)){

        }else{
            if(is_string($message)){
                $message = new Text(['content' => $message]);
            }
            $class = get_class($message);
        }
        $handle = 'transform' . substr($class, strlen('OpenWeChat\Message\Type\\'));

        return method_exists($this, $handle) ? $this->$handle($message) : [];
    }

    protected function transformText(AbstractMessage $message)
    {
        return [
            'Content' => $message->get('content'),
        ];
    }
    
}