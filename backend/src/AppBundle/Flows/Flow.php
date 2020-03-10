<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 16:32
 */

namespace AppBundle\Flows;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Subscribers;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;

/**
 * Class Flow
 * @package AppBundle\Flows
 */
class Flow implements FlowsInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Flows
     */
    protected $flow;

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
     * Flow constructor.
     * @param EntityManager $em
     * @param Flows $flow
     * @param $subscriber
     * @param string $typePush
     * @param null $user_ref
     * @param null $tag
     * @param string $messageType
     */
    public function __construct(EntityManager $em, Flows $flow, $subscriber, $typePush = Message::NOTIFY_REGULAR, $user_ref = null, $tag = null, $messageType = Message::TYPE_RESPONSE)
    {
        $this->em = $em;
        $this->flow = $flow;
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
    public function sendStartStep()
    {
        if($this->getFlow()->getStatus() == true){
            $flowStartStep = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$this->flow, 'startStep'=>true]);

            if($flowStartStep instanceof FlowItems){
                $flowsItem = new FlowsItem($this->em, $flowStartStep, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                return $flowsItem->send();
            }
        }

        return ['result' => false];
    }

    /**
     * @return array|mixed
     */
    public function getJSON()
    {
        $startFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$this->flow, 'startStep'=>true]);
        if($startFlowItem instanceof FlowItems){
            $flowsItem = new FlowsItem($this->em, $startFlowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
            $adsJSON = $flowsItem->getJSON();
        }
        else{
            $adsJSON = ['result'=>false, 'adsJSON'=>'Start step not found'];
        }

        return $adsJSON;
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
     * @return Flows
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * @param Flows $flow
     */
    public function setFlow($flow)
    {
        $this->flow = $flow;
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
