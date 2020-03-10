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
use AppBundle\Entity\UploadAttachments;
use AppBundle\Flows\Util\ButtonGenerator;
use AppBundle\Helper\Message\MyStructuredMessage;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;
use pimax\Messages\MessageMediaElement;
use pimax\Messages\StructuredMessage;

/**
 * Class SendMessageMedia
 * @package AppBundle\Flows\Type\SendMessage
 */
class SendMessageMedia implements SendMessageInterface
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
    protected $elements = array();

    /**
     * SendMessageMedia constructor.
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
        if(isset($item['params']) && ((isset($item['params']['url']) && !empty($item['params']['url'])) || (isset($item['params']['img_url']) && !empty($item['params']['img_url'])))){
            if(isset($item['params']['url']) && !empty($item['params']['url'])){
                $url = $item['params']['url'];
            }
            else{
               $url = $item['params']['img_url'];
            }
            $attachmentID = '';
            $checkUpload = $this->em->getRepository("AppBundle:UploadAttachments")->findOneBy(['page_id'=>$this->page->getPageId(), 'url'=>$url]);
            if($checkUpload instanceof UploadAttachments){
                $attachmentID = $checkUpload->getAttachmentId();
                $url = '';
            }
            //GENERATE BUTTONS
            $buttonGenerator = new ButtonGenerator();
            $itemID = (isset($item['uuid'])) ? $item['uuid'] : 0;
            $buttonsItems = $buttonGenerator->generateButtonItems($this->em, $this->page, $this->flowItem, $item, $this->subscriber, $itemID);

            $this->addElements(new MessageMediaElement($item['type'], $url, $attachmentID, $buttonsItems));

            if($this->subscriber instanceof Subscribers){
                $this->setSendItem(new MyStructuredMessage($this->subscriber->getSubscriberId(), StructuredMessage::TYPE_MEDIA, ['elements' => $this->elements], $this->quickReplies, $this->tag, $this->typePush, $this->messageType));
            }
            else{
                $this->setSendItem(new MyStructuredMessage($this->subscriber, StructuredMessage::TYPE_MEDIA, ['elements' => $this->elements], $this->quickReplies, $this->tag, $this->typePush, $this->messageType));

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
        $item = $this->getItem();
        if((isset($item['params']) && isset($item['params']['url']) && !empty($item['params']['url'])) || (isset($item['params']['img_url']) && !empty($item['params']['img_url']))){
            if(isset($item['params']['url']) && !empty($item['params']['url'])){
                $url = $item['params']['url'];
            }
            else{
                $url = $item['params']['img_url'];
            }
            $attachmentID = '';
            $checkUpload = $this->em->getRepository("AppBundle:UploadAttachments")->findOneBy(['page_id'=>$this->page->getPageId(), 'url'=>$url]);
            if($checkUpload instanceof UploadAttachments){
                $attachmentID = $checkUpload->getAttachmentId();
                $url = '';
            }
            //GENERATE BUTTONS
            $buttonGenerator = new ButtonGenerator();
            $itemID = (isset($item['uuid'])) ? $item['uuid'] : 0;
            $buttonsResult = $buttonGenerator->generateJSONButtonItems($this->flowItem, $item, $itemID);
            $buttonsItems = $buttonsResult['buttonsItems'];
            if($buttonsResult['checkButton'] == true){
                $checkButton = true;
            }

            $elements[] = new MessageMediaElement($item['type'], $url, $attachmentID, $buttonsItems);
            if($this->subscriber instanceof Subscribers){
                $message = new MyStructuredMessage($this->subscriber->getSubscriberId(), StructuredMessage::TYPE_MEDIA, ['elements' => $elements], $this->quickReplies);
            }
            else{
                $message = new MyStructuredMessage($this->subscriber, StructuredMessage::TYPE_MEDIA, ['elements' => $elements], $this->quickReplies);
            }
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
     * @param MessageMediaElement $messageElement
     */
    public function addElements(MessageMediaElement $messageElement){
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
