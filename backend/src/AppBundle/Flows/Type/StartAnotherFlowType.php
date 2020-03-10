<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 16:43
 */

namespace AppBundle\Flows\Type;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Flows\Flow;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;

/**
 * Class StartAnotherFlowType
 * @package AppBundle\Flows\Type
 */
class StartAnotherFlowType implements FlowsTypeInterface
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
     * StartAnotherFlowType constructor.
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
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function send()
    {
        if(!empty($this->flowItem->getItems())){
            if(isset($this->flowItem->getItems()[0]) && !empty($this->flowItem->getItems()[0])){
                $item = $this->flowItem->getItems()[0];
                if(isset($item['id_select_flow']) && !empty($item['id_select_flow'])){
                    $anotherFlow = $this->em->getRepository("AppBundle:Flows")->findOneBy(['page_id'=>$this->page->getPageId(), 'id'=>$item['id_select_flow'], 'status'=>true]);
                    if($anotherFlow instanceof Flows){
                        $flow = new Flow($this->em, $anotherFlow, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                        return $flow->sendStartStep();
                    }
                }
            }
        }
        return ['result' => false];
    }

    /**
     * @return array|mixed
     */
    public function getJSON()
    {
        return [
            'result' => false,
            'adsJSON' => "Der Start Sequenz sollte Sende Nachricht sein"
        ];
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
