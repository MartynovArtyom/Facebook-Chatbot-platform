<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 16:32
 */

namespace AppBundle\Flows\Type\SendMessage;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Flows\Util\ButtonGenerator;
use AppBundle\Flows\Util\TextVarReplacement;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;
use pimax\Messages\QuickReply;
use pimax\Messages\StructuredMessage;

/**
 * Class SendMessageText
 * @package AppBundle\Flows\SendMessage\Type
 */
class SendMessageText implements SendMessageInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var FlowItems
     */
    protected $flowItem;

    /**
     * @var array
     */
    protected $item;

    /**
     * @var string|Subscribers
     */
    protected $subscriber;

    /**
     * @var array
     */
    protected $quickReplies = array();

    /**
     * @var string
     */
    protected $typePush = null;

    /**
     * @var null
     */
    protected $tag = null;

    /**
     * @var string
     */
    protected $messageType = Message::TYPE_RESPONSE;

    /**
     * @var null
     */
    protected $sendItem = null;

    /**
     * SendMessageText constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param $item
     * @param $subscriber
     * @param array $quickReplies
     * @param string $typePush
     * @param null $tag
     * @param string $messageType
     */
    public function __construct(EntityManager $em, Page $page, FlowItems $flowItem, $item, $subscriber, $quickReplies = array(), $typePush = Message::NOTIFY_REGULAR, $tag = null, $messageType = Message::TYPE_RESPONSE)
    {
        $this->em = $em;
        $this->page = $page;
        $this->flowItem = $flowItem;
        $this->item = $item;
        $this->subscriber = $subscriber;
        $this->quickReplies = $quickReplies;
        $this->typePush = $typePush;
        $this->tag = $tag;
        $this->messageType = $messageType;
    }


    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function getSendData()
    {
        $item = $this->getItem();
        if(isset($item['params']) && isset($item['params']['description']) && !empty($item['params']['description'])){
            //REPLACE VARS
            $textVarReplacement = new TextVarReplacement();
            $textMessage = $textVarReplacement->replaceTextVar($this->em, $item['params']['description'], $this->page, $this->subscriber);
            //GENERATE BUTTONS
            $buttonGenerator = new ButtonGenerator();
            $itemID = (isset($item['uuid'])) ? $item['uuid'] : 0;
            $buttonsItems = $buttonGenerator->generateButtonItems($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $itemID);
            //CHOICE TYPE TEXT MESSAGE
            if($this->subscriber instanceof Subscribers){
                $subscriberID = $this->subscriber->getSubscriberId();
            }
            else{
                $subscriberID = $this->getSubscriber();
            }
            if(!empty($buttonsItems)){
                $this->setSendItem(new StructuredMessage($subscriberID,
                    StructuredMessage::TYPE_BUTTON,
                    [
                        'text' => $textMessage,
                        'buttons' => $buttonsItems
                    ],
                    $this->quickReplies,
                    $this->tag,
                    $this->typePush,
                    $this->messageType
                ));
            }
            elseif(!empty($this->quickReplies)){
                $this->setSendItem(new QuickReply($subscriberID, $textMessage, $this->quickReplies, $this->tag, $this->typePush, $this->messageType));
            }
            else{
                $this->setSendItem(new Message($subscriberID, $textMessage, false, $this->tag, $this->typePush, $this->messageType));
            }
        }

        return $this->sendItem;
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getJSONData()
    {
        $result = false;
        $message = "To set up the Ads JSON you need to attach at least one button or Quick Reply to an opt-in message";
        $checkButton = false;
        $item = $this->getItem();
        if(isset($item['params']) && isset($item['params']['description']) && !empty($item['params']['description'])){
            //REPLACE VARS
            $textVarReplacement = new TextVarReplacement();
            if($textVarReplacement->checkTextVar($item['params']['description'])){
                //GENERATE BUTTONS
                $buttonGenerator = new ButtonGenerator();
                $itemID = (isset($item['uuid'])) ? $item['uuid'] : 0;
                $buttonsResult = $buttonGenerator->generateJSONButtonItems($this->flowItem, $this->item, $itemID);
                if(isset($buttonsResult['result']) && $buttonsResult['result'] == true){
                    $buttonsItems = $buttonsResult['buttonsItems'];
                    if($buttonsResult['checkButton'] == true){
                        $checkButton = true;
                    }
                    if($this->subscriber instanceof Subscribers){
                        $subscriberID = $this->subscriber->getSubscriberId();
                    }
                    else{
                        $subscriberID = $this->getSubscriber();
                    }
                    if(!empty($buttonsItems)){
                        $message = new StructuredMessage($subscriberID,
                            StructuredMessage::TYPE_BUTTON,
                            [
                                'text' => $item['params']['description'],
                                'buttons' => $buttonsItems
                            ],
                            $this->getQuickReplies(),
                            $this->tag
                        );
                        $result = true;
                    }
                    elseif(!empty($this->quickReplies)){
                        $message = new QuickReply($subscriberID, $item['params']['description'], $this->quickReplies);
                        $result = true;
                    }
                    else{
                        $message = new Message($subscriberID, $item['params']['description']);
                        $result = true;
                    }
                }
                else{
                    $message = "You cannot use Variables in JSON Growth Tool Opt-In message";
                    $result = false;
                }
            }
            else{
                $message = "You cannot use Variables in JSON Growth Tool Opt-In message";
                $result = false;
            }
        }

        return ['result'=>$result, 'message'=>$message, 'checkButton'=>$checkButton];
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @param EntityManager $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return FlowItems
     */
    public function getFlowItem()
    {
        return $this->flowItem;
    }

    /**
     * @param FlowItems $flowItem
     */
    public function setFlowItem($flowItem)
    {
        $this->flowItem = $flowItem;
    }

    /**
     * @return array
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param array $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }

    /**
     * @return Subscribers|string
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * @param Subscribers|string $subscriber
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @return array
     */
    public function getQuickReplies()
    {
        return $this->quickReplies;
    }

    /**
     * @param array $quickReplies
     */
    public function setQuickReplies($quickReplies)
    {
        $this->quickReplies = $quickReplies;
    }

    /**
     * @return string
     */
    public function getTypePush()
    {
        return $this->typePush;
    }

    /**
     * @param string $typePush
     */
    public function setTypePush($typePush)
    {
        $this->typePush = $typePush;
    }

    /**
     * @return null
     */
    public function getSendItem()
    {
        return $this->sendItem;
    }

    /**
     * @param null $sendItem
     */
    public function setSendItem($sendItem)
    {
        $this->sendItem = $sendItem;
    }

    /**
     * @return null
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param null $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * @param string $messageType
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;
    }
}
