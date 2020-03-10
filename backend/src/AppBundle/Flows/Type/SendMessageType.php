<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 12:35
 */

namespace AppBundle\Flows\Type;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\InsightsMessage;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Flows\Type\SendMessage\SendMessageAudio;
use AppBundle\Flows\Type\SendMessage\SendMessageCard;
use AppBundle\Flows\Type\SendMessage\SendMessageDelay;
use AppBundle\Flows\Type\SendMessage\SendMessageFile;
use AppBundle\Flows\Type\SendMessage\SendMessageInterface;
use AppBundle\Flows\Type\SendMessage\SendMessageList;
use AppBundle\Flows\Type\SendMessage\SendMessageMedia;
use AppBundle\Flows\Type\SendMessage\SendMessageText;
use AppBundle\Flows\Type\SendMessage\SendMessageUserInput;
use AppBundle\Flows\Util\QuickReplyGenerator;
use AppBundle\Flows\Util\UploaderAttachmentItems;
use AppBundle\Helper\MyFbBotApp;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;

/**
 * Class SendMessageType
 * @package AppBundle\Flows\Type
 */
class SendMessageType implements FlowsTypeInterface
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
     * @var string|Subscribers
     */
    protected $subscriber;

    /**
     * @var string
     */
    protected $typePush = null;

    /**
     * @var null
     */
    protected $user_ref = null;

    /**
     * @var null
     */
    protected $tag = null;

    /**
     * @var string
     */
    protected $messageType = Message::TYPE_RESPONSE;

    /**
     * @var MyFbBotApp
     */
    protected $bot;

    /**
     * @var array
     */
    protected $quickReply = array();

    /**
     * @var null|string|array
     */
    protected $fb_message_id = null;

    /**
     * @var null|string
     */
    protected $recipient_id = null;

    /**
     * @var bool
     */
    protected $result = false;

    /**
     * SendMessageType constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param $subscriber
     * @param string $typePush
     * @param null $user_ref
     * @param null $tag
     * @param string $messageType
     */
    public function __construct(EntityManager $em, Page $page, FlowItems $flowItem, $subscriber, $typePush = Message::NOTIFY_REGULAR , $user_ref = null, $tag = null, $messageType = Message::TYPE_RESPONSE)
    {
        $this->em = $em;
        $this->page = $page;
        $this->flowItem = $flowItem;
        $this->subscriber = $subscriber;
        $this->typePush = $typePush;
        $this->user_ref = $user_ref;
        $this->tag = $tag;
        $this->messageType = $messageType;

        $this->bot =  new MyFbBotApp($this->page->getAccessToken());
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function send(){
        //GENERATE QUICK REPLY
        $this->generateQuickReply();

        //UPLOAD ATTACHMENTS
        $this->uploadAttachment();

        //SEND DIFF FLOW ITEMS ITEM TYPE
        $checkUserInput = $this->sendFlowItems();

        //SET STATS
        $this->setStatsFlow();

        return ['result'=>$this->result, 'fb_id'=>$this->fb_message_id, 'recipient_id'=>$this->recipient_id, 'checkUserInput' => $checkUserInput];
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    public function getJSON()
    {
        $adsJSON = [];
        $checkButton = false;
        //GENERATE QUICK REPLY
        $quickReplyGenerator = new QuickReplyGenerator();
        $quickReplyResult = $quickReplyGenerator->generateJSONQuickReplyItems($this->flowItem);
        if(array_key_exists('result', $quickReplyResult) && $quickReplyResult['result'] == true && array_key_exists('items', $quickReplyResult)){
            $this->setQuickReply($quickReplyResult['items']);
        }
        else{
            return [
                'result' => false,
                'adsJSON' => "You cannot use Variables in JSON Growth Tool Opt-In message"
            ];
        }
        //GET JSON DIFF FLOW ITEMS ITEM TYPE
        if(!empty($this->flowItem->getItems())) {
            foreach($this->flowItem->getItems() as $key=>$item) {
                if(isset($item['type']) && !empty($item['type'])) {
                    $jsonItem = null;
                    //GENERATE JSON ITEM
                    switch ($item['type']){
                        //TYPE TEXT
                        case "text":
                            if (count($this->flowItem->getItems()) - 1 == $key) {
                                $sendMessageText = new SendMessageText($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $this->quickReply, $this->typePush);
                            } else {
                                $sendMessageText = new SendMessageText($this->em, $this->page, $this->flowItem, $item, $this->subscriber, [], $this->typePush);
                            }
                            $jsonItem = $sendMessageText->getJSONData();
                            break;
                        //TYPE IMAGE | VIDEO
                        case "image":
                        case "video":
                            if (count($this->flowItem->getItems()) - 1 == $key) {
                                $sendMessageMedia = new SendMessageMedia($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $this->quickReply, $this->typePush);
                            } else {
                                $sendMessageMedia = new SendMessageMedia($this->em, $this->page, $this->flowItem, $item, $this->subscriber, [], $this->typePush);
                            }
                            $jsonItem = $sendMessageMedia->getJSONData();
                            break;
                        //TYPE AUDIO
                        case "audio":
                            if (count($this->flowItem->getItems()) - 1 == $key) {
                                $sendMessageAudio = new SendMessageAudio($this->em, $this->page, $item, $this->subscriber, $this->quickReply, $this->typePush);
                            } else {
                                $sendMessageAudio = new SendMessageAudio($this->em, $this->page, $item, $this->subscriber, [], $this->typePush);
                            }
                            $jsonItem = $sendMessageAudio->getJSONData();
                            break;
                        //TYPE FILE
                        case "file":
                            if (count($this->flowItem->getItems()) - 1 == $key) {
                                $sendMessageFile = new SendMessageFile($this->em, $this->page, $item, $this->subscriber, $this->quickReply, $this->typePush);
                            } else {
                                $sendMessageFile = new SendMessageFile($this->em, $this->page, $item, $this->subscriber, [], $this->typePush);
                            }
                            $jsonItem = $sendMessageFile->getJSONData();
                            break;
                        //TYPE CARD|GALLERY
                        case "card":
                        case "gallery":
                            if (count($this->flowItem->getItems()) - 1 == $key) {
                                $sendMessageCard = new SendMessageCard($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $this->quickReply, $this->typePush);
                            } else {
                                $sendMessageCard = new SendMessageCard($this->em, $this->page, $this->flowItem, $item, $this->subscriber, [], $this->typePush);
                            }
                            $jsonItem = $sendMessageCard->getJSONData();
                            break;
                        //TYPE LIST
                        case "list":
                            if (count($this->flowItem->getItems()) - 1 == $key) {
                                $sendMessageList = new SendMessageList($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $this->quickReply, $this->typePush);
                            } else {
                                $sendMessageList = new SendMessageList($this->em, $this->page, $this->flowItem, $item, $this->subscriber, [], $this->typePush);
                            }
                            $jsonItem = $sendMessageList->getJSONData();
                            break;
                        //TYPE DELAY | USER INPUT
                        case "delay":
                            $sendMessageDelay = new SendMessageDelay($this->bot, $this->subscriber, $item, $this->user_ref);
                            $jsonItem =  $sendMessageDelay->getJSONData();
                            break;
                        case "user_input":
                            $jsonItem = [
                                'result' => false,
                                'message' => "You cannot use Delays, User Inputs and Actions in Url buttons in Facebook Ads JSON Growth Tool Opt-In message"
                            ];
                            break;
                    }
                    //GET SEND JSON ITEM
                    if(isset($jsonItem['result']) && isset($jsonItem['message'])){
                        if($jsonItem['result'] == true){
                            if(method_exists($jsonItem['message'], 'getData')){
                                $JSONData = $jsonItem['message']->getData();
                                if(isset($JSONData['attachment'])){
                                    $adsJSON[] = ['message'=>$JSONData];
                                }
                                elseif(isset($JSONData['message'])){
                                    $adsJSON[] = ['message'=>$JSONData['message']];
                                }
                                if(isset($jsonItem['checkButton']) && $jsonItem['checkButton'] == true){
                                    $checkButton = true;
                                }
                            }
                        }
                        else{
                            return [
                                'result' => $jsonItem['result'],
                                'adsJSON' => $jsonItem['message']
                            ];
                        }
                    }
                }
            }
        }

        if(!empty($adsJSON) && (!empty($this->quickReply) || $checkButton == true)){
            return [
                'result' => true,
                'adsJSON' => $adsJSON
            ];
        }
        else{
            return [
                'result' => false,
                'adsJSON' => "To set up the Ads JSON you need to attach at least one button or Quick Reply to an opt-in message"
            ];
        }
    }

    /**
     * Generate Quick Reply
     */
    private function generateQuickReply(){
        $quickReplyGenerator = new QuickReplyGenerator();
        $this->setQuickReply($quickReplyGenerator->generateQuickReplyItems($this->em, $this->page, $this->flowItem, $this->subscriber));
    }


    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function uploadAttachment(){
        $uploaderAttachmentItems = new UploaderAttachmentItems();
        $uploaderAttachmentItems->upload($this->em, $this->page, $this->flowItem);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function sendFlowItems(){
        if(!empty($this->flowItem->getItems())) {
            foreach($this->flowItem->getItems() as $key=>$item) {
                if(isset($item['type']) && !empty($item['type'])) {
                    //GENERATE SEND MESSAGE OBJECT
                    $sendMessageObject = $this->generateSendItem($item, $key);
                    if($sendMessageObject instanceof SendMessageInterface){
                        //SEND ITEM
                        $this->sendItem($sendMessageObject->getSendData());

                        //CHECK USER INPUT
                        if($sendMessageObject instanceof SendMessageUserInput){
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param $item
     * @param $key
     * @return mixed|null
     * @throws \Exception
     */
    private function generateSendItem($item, $key){
        $sendMessageObject = null;
        if(isset($item['type']) && !empty($item['type'])) {
            //GENERATE SEND ITEM
            switch ($item['type']) {
                //TYPE TEXT
                case "text":
                    if (count($this->getFlowItem()->getItems()) - 1 == $key) {
                        $sendMessageObject = new SendMessageText($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $this->quickReply, $this->typePush, $this->tag, $this->messageType);
                    } else {
                        $sendMessageObject = new SendMessageText($this->em, $this->page, $this->flowItem, $item, $this->subscriber, [], $this->typePush, $this->tag, $this->messageType);
                    }
                    break;
                //TYPE IMAGE | VIDEO
                case "image":
                case "video":
                    if (count($this->getFlowItem()->getItems()) - 1 == $key) {
                        $sendMessageObject = new SendMessageMedia($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $this->quickReply, $this->typePush, $this->tag, $this->messageType);
                    } else {
                        $sendMessageObject = new SendMessageMedia($this->em, $this->page, $this->flowItem, $item, $this->subscriber, [], $this->typePush, $this->tag, $this->messageType);
                    }
                    break;
                //TYPE AUDIO
                case "audio":
                    if (count($this->getFlowItem()->getItems()) - 1 == $key) {
                        $sendMessageObject = new SendMessageAudio($this->em, $this->page, $item, $this->subscriber, $this->quickReply, $this->typePush, $this->tag, $this->messageType);
                    } else {
                        $sendMessageObject = new SendMessageAudio($this->em, $this->page, $item, $this->subscriber, [], $this->typePush, $this->tag, $this->messageType);
                    }
                    break;
                //TYPE FILE
                case "file":
                    if (count($this->getFlowItem()->getItems()) - 1 == $key) {
                        $sendMessageObject = new SendMessageFile($this->em, $this->page, $item, $this->subscriber, $this->quickReply, $this->typePush, $this->tag, $this->messageType);
                    } else {
                        $sendMessageObject = new SendMessageFile($this->em, $this->page, $item, $this->subscriber, [], $this->typePush, $this->tag, $this->messageType);
                    }
                    break;
                //TYPE CARD|GALLERY
                case "card":
                case "gallery":
                    if (count($this->getFlowItem()->getItems()) - 1 == $key) {
                        $sendMessageObject = new SendMessageCard($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $this->quickReply, $this->typePush, $this->tag, $this->messageType);
                    } else {
                        $sendMessageObject = new SendMessageCard($this->em, $this->page, $this->flowItem, $item, $this->subscriber, [], $this->typePush, $this->tag, $this->messageType);
                    }
                    break;
                //TYPE LIST
                case "list":
                    if (count($this->getFlowItem()->getItems()) - 1 == $key) {
                        $sendMessageObject = new SendMessageList($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $this->quickReply, $this->typePush, $this->tag, $this->messageType);
                    } else {
                        $sendMessageObject = new SendMessageList($this->em, $this->page, $this->flowItem, $item, $this->subscriber, [], $this->typePush, $this->tag, $this->messageType);
                    }
                    break;
                //TYPE DELAY
                case "delay":
                    $sendMessageObject = new SendMessageDelay($this->bot, $this->subscriber, $item, $this->user_ref);
                    break;
                //TYPE USER INPUT
                case "user_input":
                    $sendMessageObject = new SendMessageUserInput($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $this->typePush, false, $this->tag, $this->messageType);
                    break;
            }
        }

        return $sendMessageObject;
    }

    /**
     * @param $sendItem
     */
    private function sendItem($sendItem){
        if(!is_null($sendItem)){
            $result_send = $this->bot->send($sendItem, $this->user_ref);
            if(isset($result_send['message_id'])){
                $this->setFbMessageId($result_send['message_id']);
                $this->setResult(true);
                if(isset($result_send['recipient_id'])){
                    $this->setRecipientId($result_send['recipient_id']);
                }
            }
            else{
                $this->setResult(false);
                $this->setFbMessageId($result_send);
            }
        }
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function setStatsFlow(){
        if(!empty($this->flowItem->getId())){
            $this->em->getRepository("AppBundle:FlowItems")->updateSentCountById($this->flowItem->getId());
            if($this->isResult()){
                if($this->subscriber instanceof Subscribers){
                    $insights = new InsightsMessage($this->flowItem, $this->subscriber->getSubscriberId());
                }
                else{
                    if(!is_null($this->getRecipientId())){
                        $insights = new InsightsMessage($this->flowItem, $this->recipient_id);
                    }
                    else{
                        $insights = new InsightsMessage($this->flowItem, $this->subscriber);
                    }
                }
                $this->em->persist($insights);
                $this->em->flush();
            }
            else{
                if(isset($this->fb_message_id['error']) && !empty($this->fb_message_id['error'])){
                    $error = $this->fb_message_id['error'];
                    if(isset($error['code']) && $error['code'] == 551){
                        if($this->subscriber instanceof Subscribers){
                            $this->subscriber->setStatus(false);
                            $this->em->persist($this->subscriber);
                            $this->em->flush();
                        }
                    }
                }
            }
        }
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
    public function getUserRef()
    {
        return $this->user_ref;
    }

    /**
     * @param null $user_ref
     */
    public function setUserRef($user_ref)
    {
        $this->user_ref = $user_ref;
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
     * @return array
     */
    public function getQuickReply()
    {
        return $this->quickReply;
    }

    /**
     * @param array $quickReply
     */
    public function setQuickReply($quickReply)
    {
        $this->quickReply = $quickReply;
    }

    /**
     * @return array|null|string
     */
    public function getFbMessageId()
    {
        return $this->fb_message_id;
    }

    /**
     * @param array|null|string $fb_message_id
     */
    public function setFbMessageId($fb_message_id)
    {
        $this->fb_message_id = $fb_message_id;
    }

    /**
     * @return null|string
     */
    public function getRecipientId()
    {
        return $this->recipient_id;
    }

    /**
     * @param null|string $recipient_id
     */
    public function setRecipientId($recipient_id)
    {
        $this->recipient_id = $recipient_id;
    }

    /**
     * @return bool
     */
    public function isResult()
    {
        return $this->result;
    }

    /**
     * @param bool $result
     */
    public function setResult($result)
    {
        $this->result = $result;
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
