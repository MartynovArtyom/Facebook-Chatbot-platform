<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 16:32
 */

namespace AppBundle\Flows\Type\SendMessage;


use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\UploadAttachments;
use AppBundle\Helper\Message\MyAudioMessage;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;

/**
 * Class SendMessageAudio
 * @package AppBundle\Flows\Type\SendMessage
 */
class SendMessageAudio implements SendMessageInterface
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
     * SendMessageAudio constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param $item
     * @param $subscriber
     * @param array $quickReplies
     * @param string $typePush
     * @param null $tag
     * @param string $messageType
     */
    public function __construct(EntityManager $em, Page $page, $item, $subscriber, $quickReplies = array(), $typePush = Message::NOTIFY_REGULAR, $tag = null, $messageType = Message::TYPE_RESPONSE)
    {
        $this->em = $em;
        $this->page = $page;
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
        if(isset($item['params']) && isset($item['params']['url']) && !empty($item['params']['url'])){
            $checkUpload = $this->em->getRepository("AppBundle:UploadAttachments")->findOneBy(['page_id'=>$this->page->getPageId(), 'url'=>$item['params']['url']]);
            if($checkUpload instanceof UploadAttachments){
                $item['params']['url'] = $checkUpload->getAttachmentId();
            }
            if($this->subscriber instanceof Subscribers){
                $this->setSendItem(new MyAudioMessage($this->subscriber->getSubscriberId(), $item['params']['url'], $this->quickReplies, $this->tag, $this->typePush, $this->messageType));
            }
            else{
                $this->setSendItem(new MyAudioMessage($this->subscriber, $item['params']['url'], $this->quickReplies, $this->tag, $this->typePush, $this->messageType));
            }
        }

        return $this->getSendItem();
    }

    /**
     * @return array|mixed
     */
    public function getJSONData()
    {
        $result = false;
        $message = "To set up the Ads JSON you need to attach at least one button or Quick Reply to an opt-in message";
        $item = $this->getItem();
        if(isset($item['params']) && isset($item['params']['url']) && !empty($item['params']['url'])){
            $checkUpload = $this->em->getRepository("AppBundle:UploadAttachments")->findOneBy(['page_id'=>$this->page->getPageId(), 'url'=>$item['params']['url']]);
            if($checkUpload instanceof UploadAttachments){
                $item['params']['url'] = $checkUpload->getAttachmentId();
            }
            if($this->subscriber instanceof Subscribers){
                $message = new MyAudioMessage($this->subscriber->getSubscriberId(), $item['params']['url'], $this->quickReplies);
            }
            else{
                $message = new MyAudioMessage($this->subscriber, $item['params']['url'], $this->quickReplies);
            }
            $result = true;
        }

        return ['result'=>$result, 'message'=>$message];
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
