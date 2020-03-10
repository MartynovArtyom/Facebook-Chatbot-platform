<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 16:32
 */

namespace AppBundle\Flows\Type\SendMessage;


use AppBundle\Entity\Subscribers;
use AppBundle\Helper\MyFbBotApp;
use pimax\Messages\SenderAction;

/**
 * Class SendMessageDelay
 * @package AppBundle\Flows\Type\SendMessage
 */
class SendMessageDelay implements SendMessageInterface
{
    /**
     * @var MyFbBotApp
     */
    protected $bot;

    /**
     * @var null|string|Subscribers
     */
    protected $subscriber;

    /**
     * @var null|array
     */
    protected $item = null;

    /**
     * @var null|string
     */
    protected $user_ref = null;

    /**
     * FlowSendMessageTypeDelay constructor.
     * @param MyFbBotApp $bot
     * @param Subscribers|null|string $subscriber
     * @param array|null $item
     * @param null|string $user_ref
     */
    public function __construct(MyFbBotApp $bot, $subscriber, $item, $user_ref = null)
    {
        $this->bot = $bot;
        $this->subscriber = $subscriber;
        $this->item = $item;
        $this->user_ref = $user_ref;
    }

    /**
     * @return mixed|void
     */
    public function getSendData()
    {
        $item = $this->getItem();
        if (isset($item['params']) && isset($item['params']['time']) && !empty($item['params']['time'])){
            if($this->subscriber instanceof Subscribers){
                $subscriberID = $this->subscriber->getSubscriberId();
            }
            else{
                $subscriberID = $this->subscriber;
            }
            $this->bot->send(new SenderAction($subscriberID, SenderAction::ACTION_MARK_SEEN), $this->user_ref);
            if(isset($item['params']['type_action']) && $item['params']['type_action'] == true){
                $this->bot->send(new SenderAction($subscriberID, SenderAction::ACTION_TYPING_ON), $this->user_ref);
            }
            sleep($item['params']['time']);
            if(isset($item['params']['type_action']) && $item['params']['type_action'] == true){
                $this->bot->send(new SenderAction($subscriberID, SenderAction::ACTION_TYPING_OFF), $this->user_ref);
            }
        }
    }

    /**
     * @return array|mixed
     */
    public function getJSONData()
    {
        return [
            'result' => false,
            'message' => "You cannot use Delays, User Inputs and Actions in Url buttons in Facebook Ads JSON Growth Tool Opt-In message"
        ];
    }

    /**
     * @return MyFbBotApp
     */
    public function getBot()
    {
        return $this->bot;
    }

    /**
     * @param MyFbBotApp $bot
     */
    public function setBot($bot)
    {
        $this->bot = $bot;
    }

    /**
     * @return Subscribers|null|string
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * @param Subscribers|null|string $subscriber
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @return array|null
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param array|null $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return null|string
     */
    public function getUserRef()
    {
        return $this->user_ref;
    }

    /**
     * @param null|string $user_ref
     */
    public function setUserRef($user_ref)
    {
        $this->user_ref = $user_ref;
    }
}