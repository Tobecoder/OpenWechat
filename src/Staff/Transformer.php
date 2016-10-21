<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/11
 * Time: 18:05
 */
namespace OpenWechat\Staff;
use OpenWechat\Message\Type\AbstractMessage;
use OpenWechat\Message\Type\Text;

class Transformer
{
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
            'msgtype' => 'text',
            'text' => [
                'content' => $message->get('content')
            ]
        ];
    }
}