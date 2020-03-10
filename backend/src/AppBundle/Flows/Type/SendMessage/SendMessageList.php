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
use pimax\Messages\MessageDefaultAction;
use pimax\Messages\MessageElement;
use pimax\Messages\StructuredMessage;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SendMessageList
 * @package AppBundle\Flows\Type\SendMessage
 */
class SendMessageList implements SendMessageInterface
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
     * @var array
     */
    protected $elements = [];

    /**
     * SendMessageList constructor.
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
        $itemID = (isset($item['uuid'])) ? $item['uuid'] : 0;
        $textVarReplacement = new TextVarReplacement();
        $buttonGenerator = new ButtonGenerator();
        $request = Request::createFromGlobals();
        if(isset($item['params']) &&  isset($item['params']['list_array']) && !empty($item['params']['list_array'])){
            foreach ($item['params']['list_array'] as $list){
                $title = (isset($list['title'])) ? $list['title'] : "";
                //REPLACE TITLE VAR
                $title = $textVarReplacement->replaceTextVar($this->em, $title, $this->page, $this->subscriber);

                $subtitle = (isset($list['subtitle'])) ? $list['subtitle'] : "";
                //REPLACE SUBTITLE VAR
                $subtitle = $textVarReplacement->replaceTextVar($this->em, $subtitle, $this->page, $this->subscriber);

                $image = (isset($list['img_url'])) ? $list['img_url'] : "";
                $url = (isset($list['url_page'])) ? $list['url_page'] : "";
                //GENERATE BUTTONS
                $buttonsItems = [];
                if(isset($list['buttons']) && !empty($list['buttons'])){
                    $buttonsItems = $buttonGenerator->generateButtonItems($this->em, $this->page, $this->flowItem, $list, $this->subscriber, $itemID);
                }
                if(!empty($url)){
                    $url = $request->getSchemeAndHttpHost()."/v2/fetch/insights/button?url=".urlencode($url);
                }

                $this->addElements(new MessageElement($title, $subtitle, $image, $buttonsItems, $url));
            }
        }

        if(!empty($this->elements)){
            $buttons = [];
            if(isset($item['params']) && isset($item['params']['buttons']) && !empty($item['params']['buttons'])){
                $buttons = $buttonGenerator->generateButtonItems($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $itemID);
            }
            $data = [
                'elements' => $this->elements,
                'buttons' => $buttons,
                'top_element_style' => 'compact'
            ];
            if($this->subscriber instanceof Subscribers){
                $this->setSendItem(new StructuredMessage($this->subscriber->getSubscriberId(), StructuredMessage::TYPE_LIST, $data, $this->quickReplies, $this->tag, $this->typePush, $this->messageType));
            }
            else{
                $this->setSendItem(new StructuredMessage($this->subscriber, StructuredMessage::TYPE_LIST, $data, $this->quickReplies, $this->tag, $this->typePush, $this->messageType));
            }
        }

        return $this->getSendItem();
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
        $elements = [];
        $textVarReplacement = new TextVarReplacement();
        $buttonGenerator = new ButtonGenerator();
        $item = $this->getItem();
        $itemID = (isset($item['uuid'])) ? $item['uuid'] : 0;
        if(isset($item['params']) &&  isset($item['params']['list_array']) && !empty($item['params']['list_array'])){
            foreach ($item['params']['list_array'] as $list){
                $title = (isset($list['title'])) ? $list['title'] : "";
                //REPLACE TITLE VAR
                if($textVarReplacement->checkTextVar($title)){
                    $subtitle = (isset($list['subtitle'])) ? $list['subtitle'] : "";
                    //REPLACE SUBTITLE VAR
                    if($textVarReplacement->checkTextVar($subtitle)){
                        $image = (isset($list['img_url'])) ? $list['img_url'] : "";
                        $url = (isset($list['url_page'])) ? $list['url_page'] : "";
                        //GENERATE BUTTONS
                        $buttonsResult = $buttonGenerator->generateJSONButtonItems($this->flowItem, $list, $itemID);
                        if(isset($buttonsResult['result']) && $buttonsResult['result'] == true){
                            $buttonsItems = $buttonsResult['buttonsItems'];
                            if($buttonsResult['checkButton'] == true){
                                $checkButton = true;
                            }
                            //CREATE DEFAULT ACTION
                            $defaultAction = [];
                            if(!empty($url)){
                                $defaultAction = new MessageDefaultAction($url, 'full', true, $url);
                                $defaultAction = $defaultAction->getData();
                            }

                            $elements[] = new MessageElement($title, $subtitle, $image, $buttonsItems, '', $defaultAction);
                        }
                        else{
                            return [
                                'result'=>false,
                                'message'=>"You cannot use Variables in JSON Growth Tool Opt-In message"
                            ];
                        }
                    }
                    else{
                        return [
                            'result'=>false,
                            'message'=>"You cannot use Variables in JSON Growth Tool Opt-In message"
                        ];
                    }
                }
                else{
                    return [
                        'result'=>false,
                        'message'=>"You cannot use Variables in JSON Growth Tool Opt-In message"
                    ];
                }
            }
        }

        if(!empty($elements)){
            $buttonsRes = $buttonGenerator->generateJSONButtonItems($this->flowItem, $item, $itemID);
            $buttons = $buttonsRes['buttonsItems'];
            if($buttonsRes['checkButton'] == true){
                $checkButton = true;
            }
            $data = [
                'elements' => $elements,
                'buttons' => $buttons,
                'top_element_style' => 'compact'
            ];
            $message = new StructuredMessage($this->subscriber, StructuredMessage::TYPE_LIST, $data, $this->quickReplies);
            $result = true;
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
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param array $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @param MessageElement $messageElement
     */
    public function addElements(MessageElement $messageElement){
        $this->elements[] = $messageElement;
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
