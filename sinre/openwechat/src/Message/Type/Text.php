<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/11
 * Time: 11:56
 */
namespace OpenWechat\Message\Type;

class Text extends AbstractMessage
{
    /**
     * Message type.
     *
     * @var string
     */
    protected $msgType = 'text';

    /**
     * Message Properties.
     *
     * @var array
     */
    protected $properties = ['content'];
}