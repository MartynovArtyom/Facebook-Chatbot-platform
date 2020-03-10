<?php
/**
 * Created by PhpStorm.
 * Date: 06.03.19
 * Time: 16:55
 */

namespace AppBundle\Flows\Type\SendMessage;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\UserInputDelay;
use AppBundle\Flows\Util\TextVarReplacement;
use AppBundle\Helper\Message\MyStructuredMessage;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;
use pimax\Messages\MessageButton;
use pimax\Messages\QuickReply;
use pimax\Messages\QuickReplyButton;
use pimax\Messages\StructuredMessage;

class SendMessageUserInput implements SendMessageInterface
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
    protected $sendItem = null;

    /**
     * @var bool
     */
    protected $skipButton = false;

    /**
     * @var null
     */
    protected $tag = null;

    /**
     * @var string
     */
    protected $messageType = Message::TYPE_RESPONSE;

    /**
     * SendMessageUserInput constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param $item
     * @param $subscriber
     * @param string $typePush
     * @param bool $skipButton
     * @param null $tag
     * @param string $messageType
     */
    public function __construct(EntityManager $em, Page $page, FlowItems $flowItem, $item, $subscriber, $typePush = Message::NOTIFY_REGULAR, $skipButton = false, $tag = null, $messageType = Message::TYPE_RESPONSE)
    {
        $this->em = $em;
        $this->page = $page;
        $this->flowItem = $flowItem;
        $this->item = $item;
        $this->subscriber = $subscriber;
        $this->typePush = $typePush;
        $this->skipButton = $skipButton;
        $this->tag = $tag;
        $this->messageType = $messageType;
    }

    /**
     * @return mixed|void|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function getSendData(){
        $item = $this->getItem();
        if( array_key_exists('uuid', $item) && !empty($item['uuid']) && array_key_exists('params', $item)
            && array_key_exists('description', $item['params']) && !empty($item['params']['description'])
            && array_key_exists('keyboardInput', $item['params']) && array_key_exists('replyType', $item['params']['keyboardInput'])
        ){
            if($this->subscriber instanceof Subscribers){
                //REPLACE VARS
                $textVarReplacement = new TextVarReplacement();
                $textMessage = $textVarReplacement->replaceTextVar($this->em, $item['params']['description'], $this->page, $this->subscriber);
                if($item['params']['keyboardInput']['replyType'] == 1){
                    $this->generateQuickReply();
                    if(!empty($this->quickReplies)){
                        $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, $this->quickReplies, $this->tag, $this->typePush, $this->messageType));
                    }
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 3){
                    $qr[] = new QuickReplyButton('user_email');
                    $skipButton = $this->generateSkipButton();
                    if(!is_null($skipButton)){
                        $qr[] = $skipButton;
                    }
                    $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, $qr, $this->tag, $this->typePush, $this->messageType));
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 4){
                    $qr[] = new QuickReplyButton('user_phone_number');
                    $skipButton = $this->generateSkipButton();
                    if(!is_null($skipButton)){
                        $qr[] = $skipButton;
                    }
                    $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, $qr, $this->tag, $this->typePush, $this->messageType));
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 8){
                    if(array_key_exists('text_on_button', $item['params']['keyboardInput']) && !empty($item['params']['keyboardInput']['text_on_button'])){
                        $buttons[] = new MessageButton(
                            MessageButton::TYPE_WEB,
                            $item['params']['keyboardInput']['text_on_button'],
                            "https://app.chatbo.de/picker_date?pageID=".$this->page->getPageId()."&flowID=".$this->flowItem->getFlow()->getId()."&flowItemUuid=".$this->flowItem->getUuid()."&itemUuid=".$item['uuid'],
                            'full',
                            true
                        );
                        $skipButton = $this->generateSkipButton();
                        if(!is_null($skipButton)){
                            $buttons[] = $skipButton;
                        }

                        $this->setSendItem(new StructuredMessage($this->subscriber->getSubscriberId(),
                            StructuredMessage::TYPE_BUTTON,
                            [
                                'text' => $textMessage,
                                'buttons' => $buttons
                            ],
                            [],
                            $this->tag,
                            $this->typePush,
                            $this->messageType
                        ));
                    }
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 9) {
                    if(array_key_exists('text_on_button', $item['params']['keyboardInput']) && !empty($item['params']['keyboardInput']['text_on_button'])){
                        $buttons[] = new MessageButton(
                            MessageButton::TYPE_WEB,
                            $item['params']['keyboardInput']['text_on_button'],
                            "https://app.chatbo.de/picker_date_time?pageID=".$this->page->getPageId()."&flowID=".$this->flowItem->getFlow()->getId()."&flowItemUuid=".$this->flowItem->getUuid()."&itemUuid=".$item['uuid'],
                            'full',
                            true
                        );
                        $skipButton = $this->generateSkipButton();
                        if(!is_null($skipButton)){
                            $buttons[] = $skipButton;
                        }

                        $this->setSendItem(new StructuredMessage($this->subscriber->getSubscriberId(),
                            StructuredMessage::TYPE_BUTTON,
                            [
                                'text' => $textMessage,
                                'buttons' => $buttons
                            ],
                            [],
                            $this->tag,
                            $this->typePush,
                            $this->messageType
                        ));

                    }
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 10){
                    $qr[] = new QuickReplyButton('location');
                    $skipButton = $this->generateSkipButton();
                    if(!is_null($skipButton)){
                        $qr[] = $skipButton;
                    }
                    $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, $qr, $this->tag, $this->typePush, $this->messageType));
                }
                else{
                    $skipButton = $this->generateSkipButton();
                    if(!is_null($skipButton)){
                        $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, [$skipButton], $this->tag, $this->typePush, $this->messageType));
                    }
                    else{
                        $this->setSendItem(new Message($this->subscriber->getSubscriberId(), $textMessage, false, $this->tag, $this->typePush, $this->messageType));
                    }
                }

                if(!empty($this->sendItem)){
                    $userInputDelay = $this->em->getRepository("AppBundle:UserInputDelay")->findOneBy([
                        'page_id' => $this->page->getPageId(),
                        'subscriber' => $this->subscriber
                    ]);
                    if($userInputDelay instanceof UserInputDelay){
                        $userInputDelay->setFlowItem($this->flowItem);
                        $userInputDelay->setItemUuid($item['uuid']);
                    }
                    else{
                        $userInputDelay = new UserInputDelay($this->page->getPageId(), $this->subscriber, $this->flowItem, $item['uuid']);
                    }
                    $this->em->persist($userInputDelay);
                    $this->em->flush();
                }
            }

        }

        return $this->sendItem;
    }

    /**
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function gerRetrySendData(){
        $item = $this->getItem();
        if( array_key_exists('uuid', $item) && !empty($item['uuid']) && array_key_exists('params', $item)
            && array_key_exists('keyboardInput', $item['params']) && array_key_exists('replyType', $item['params']['keyboardInput'])
            && array_key_exists('retry_message', $item['params']['keyboardInput']) && !empty($item['params']['keyboardInput']['retry_message'])
        ){
            if($this->subscriber instanceof Subscribers){
                //REPLACE VARS
                $textVarReplacement = new TextVarReplacement();
                $textMessage = $textVarReplacement->replaceTextVar($this->em, $item['params']['keyboardInput']['retry_message'], $this->page, $this->subscriber);
                if($item['params']['keyboardInput']['replyType'] == 1){
                    $this->generateQuickReply();
                    if(!empty($this->quickReplies)){
                        $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, $this->quickReplies, null, $this->typePush));
                    }
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 3){
                    $qr[] = new QuickReplyButton('user_email');
                    $skipButton = $this->generateSkipButton();
                    if(!is_null($skipButton)){
                        $qr[] = $skipButton;
                    }
                    $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, $qr, null, $this->typePush));
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 4){
                    $qr[] = new QuickReplyButton('user_phone_number');
                    $skipButton = $this->generateSkipButton();
                    if(!is_null($skipButton)){
                        $qr[] = $skipButton;
                    }
                    $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, $qr, null, $this->typePush));
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 8){
                    if(array_key_exists('text_on_button', $item['params']['keyboardInput']) && !empty($item['params']['keyboardInput']['text_on_button'])){
                        $buttons[] = new MessageButton(
                            MessageButton::TYPE_WEB,
                            $item['params']['keyboardInput']['text_on_button'],
                            "https://app.chatbo.de/picker_date?pageID=".$this->page->getPageId()."&flowID=".$this->flowItem->getFlow()->getId()."&flowItemUuid=".$this->flowItem->getUuid()."&itemUuid=".$item['uuid'],
                            'compact',
                            true
                        );
                        $skipButton = $this->generateSkipButton();
                        if(!is_null($skipButton)){
                            $buttons[] = $skipButton;
                        }

                        $this->setSendItem(new StructuredMessage($this->subscriber->getSubscriberId(),
                            StructuredMessage::TYPE_BUTTON,
                            [
                                'text' => $textMessage,
                                'buttons' => $buttons
                            ],
                            [],
                            null,
                            $this->typePush
                        ));
                    }
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 9) {
                    if(array_key_exists('text_on_button', $item['params']['keyboardInput']) && !empty($item['params']['keyboardInput']['text_on_button'])){
                        $buttons[] = new MessageButton(
                            MessageButton::TYPE_WEB,
                            $item['params']['keyboardInput']['text_on_button'],
                            "https://app.chatbo.de/picker_date_time?pageID=".$this->page->getPageId()."&flowID=".$this->flowItem->getFlow()->getId()."&flowItemUuid=".$this->flowItem->getUuid()."&itemUuid=".$item['uuid'],
                            'compact',
                            true
                        );
                        $skipButton = $this->generateSkipButton();
                        if(!is_null($skipButton)){
                            $buttons[] = $skipButton;
                        }

                        $this->setSendItem(new StructuredMessage($this->subscriber->getSubscriberId(),
                            StructuredMessage::TYPE_BUTTON,
                            [
                                'text' => $textMessage,
                                'buttons' => $buttons
                            ],
                            [],
                            null,
                            $this->typePush
                        ));

                    }
                }
                elseif ($item['params']['keyboardInput']['replyType'] == 10){
                    $qr[] = new QuickReplyButton('location');
                    $skipButton = $this->generateSkipButton();
                    if(!is_null($skipButton)){
                        $qr[] = $skipButton;
                    }
                    $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, $qr, null, $this->typePush));
                }
                else{
                    $skipButton = $this->generateSkipButton();
                    if(!is_null($skipButton)){
                        $this->setSendItem(new QuickReply($this->subscriber->getSubscriberId(), $textMessage, [$skipButton], null, $this->typePush));
                    }
                    else{
                        $this->setSendItem(new Message($this->subscriber->getSubscriberId(), $textMessage, false, null, $this->typePush));
                    }
                }

                if(!empty($this->sendItem)){
                    $userInputDelay = $this->em->getRepository("AppBundle:UserInputDelay")->findOneBy([
                        'page_id' => $this->page->getPageId(),
                        'subscriber' => $this->subscriber
                    ]);
                    if(!$userInputDelay instanceof UserInputDelay){
                        $userInputDelay = new UserInputDelay($this->page->getPageId(), $this->subscriber, $this->flowItem, $item['uuid']);
                        $this->em->persist($userInputDelay);
                        $this->em->flush();
                    }

                }
            }

        }

        return $this->sendItem;
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
     * Generate QR
     */
    public function generateQuickReply(){
        $item = $this->getItem();
        $quickReplies = [];
        if(array_key_exists('uuid', $item) && !empty($item['uuid']) && array_key_exists('params', $item)
            && array_key_exists('quick_reply', $item['params']) && !empty($item['params']['quick_reply'])
        ) {
            $textVarReplacement = new TextVarReplacement();
            foreach ($item['params']['quick_reply'] as $quickReply){
                if(array_key_exists('uuid', $quickReply) && !empty($quickReply['uuid'])){
                    $title = $textVarReplacement->replaceTextVar($this->em, $quickReply['title'], $this->page, $this->subscriber);
                    if(array_key_exists('title', $quickReply) && !empty($quickReply['title'])){
                        $quickReplies[] = new QuickReplyButton(
                            QuickReplyButton::TYPE_TEXT,
                            $title,
                            //CHATBO_NEW     :TYPE                     :FLOW_ID                                      :FLOW_ITEM_UUID               :ITEM_UUID       :QR_UUID
                            'CHATBO_NEW:QUICK_REPLY_USER_INPUT:'.$this->flowItem->getFlow()->getId().':'.$this->flowItem->getUuid().':'.$item['uuid'].':'.$quickReply['uuid']
                        );
                    }
                }
            }
        }
        //Skip button
        $skipButton = $this->generateSkipButton();
        if(!is_null($skipButton)){
            $quickReplies[] = $skipButton;
        }

        $this->setQuickReplies($quickReplies);
    }

    /**
     * @return MessageButton|null|QuickReplyButton
     */
    public function generateSkipButton(){
        $item = $this->getItem();
        if(array_key_exists('uuid', $item) && !empty($item['uuid']) && array_key_exists('params', $item) && array_key_exists('keyboardInput',$item['params'])
            && array_key_exists('skip_button',$item['params']['keyboardInput']) && !empty($item['params']['keyboardInput']['skip_button'])
        ){
            if(array_key_exists('replyType', $item['params']['keyboardInput']) && in_array($item['params']['keyboardInput']['replyType'], [8,9])){
                return new MessageButton(
                    MessageButton::TYPE_POSTBACK,
                    $item['params']['keyboardInput']['skip_button'],
                    //CHATBO_NEW     :TYPE                     :FLOW_ID                                      :FLOW_ITEM_UUID                   :ITEM_UUID
                    'CHATBO_NEW:BUTTON_USER_INPUT_SKIP:'.$this->flowItem->getFlow()->getId().':'.$this->flowItem->getUuid().':'.$item['uuid']
                );
            }
            else{
                if($this->skipButton){
                    return new QuickReplyButton(
                        QuickReplyButton::TYPE_TEXT,
                        $item['params']['keyboardInput']['skip_button'],
                        //CHATBO_NEW     :TYPE                     :FLOW_ID                                      :FLOW_ITEM_UUID               :ITEM_UUID
                        'CHATBO_NEW:QUICK_REPLY_USER_INPUT_SKIP:'.$this->flowItem->getFlow()->getId().':'.$this->flowItem->getUuid().':'.$item['uuid']
                    );
                }
            }
        }

        return null;
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
     * @return bool
     */
    public function isSkipButton()
    {
        return $this->skipButton;
    }

    /**
     * @param bool $skipButton
     */
    public function setSkipButton($skipButton)
    {
        $this->skipButton = $skipButton;
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
