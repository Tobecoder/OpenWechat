<?php
/**
 * Created by PhpStorm.
 * User: songzhen
 * Date: 2016/8/11
 * Time: 17:56
 */
namespace OpenWechat\Staff;
use OpenWechat\Core\Exceptions\RuntimeException;
use OpenWechat\Message\Type\Text;

class MessageBuilder
{
    /**
     * Message to send.
     *
     * @var \OpenWeChat\Message\Type\AbstractMessage;
     */
    protected $message;

    /**
     * Message target user open id.
     *
     * @var string
     */
    protected $to;

    /**
     * Message sender staff id.
     *
     * @var string
     */
    protected $account;

    /**
     * Staff instance.
     *
     * @var \OpenWeChat\Staff\Staff
     */
    protected $staff;

    /**
     * MessageBuilder constructor.
     *
     * @param \OpenWeChat\Staff\Staff $staff
     */
    public function __construct(Staff $staff)
    {
        $this->staff = $staff;
    }

    /**
     * Set message to send.
     *
     * @param string|AbstractMessage $message
     *
     * @return MessageBuilder
     *
     * @throws InvalidArgumentException
     */
    public function message($message)
    {
        if (is_string($message)) {
            $message = new Text(['content' => $message]);
        }

        $this->message = $message;

        return $this;
    }

    /**
     * Set target user open id.
     *
     * @param string $openId
     *
     * @return MessageBuilder
     */
    public function to($openId)
    {
        $this->to = $openId;

        return $this;
    }

    public function send()
    {
        if (empty($this->message)) {
            throw new RuntimeException('No message to send.');
        }

        $transformer = new Transformer();

//        if ($this->message instanceof RawMessage) {
//            $message = $this->message->get('content');
//        } else {
//            $content = $transformer->transform($this->message);
//
//            $message = array_merge([
//                'touser' => $this->to,
//                'customservice' => ['kf_account' => $this->account],
//            ], $content);
//        }
        $content = $transformer->transform($this->message);

        $message = array_merge([
            'touser' => $this->to,
            'customservice' => ['kf_account' => $this->account],
        ], $content);

        return $this->staff->send($message);
    }
}