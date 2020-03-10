<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 17:04
 */

namespace AppBundle\Flows;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Flows\Type\ConditionType;
use AppBundle\Flows\Type\FlowsTypeInterface;
use AppBundle\Flows\Type\PerformActionType;
use AppBundle\Flows\Type\RandomizerType;
use AppBundle\Flows\Type\SendMessageType;
use AppBundle\Flows\Type\SmartDelayType;
use AppBundle\Flows\Type\StartAnotherFlowType;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class FlowsItem
 * @package AppBundle\Flows
 */
class FlowsItem implements FlowsItemInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

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
     * @var Page
     */
    protected $page;

    /**
     * @var null|FlowsTypeInterface
     */
    protected $flowsItemObject = null;

    /**
     * @var array
     */
    protected $result = ['result'=>false];

    /**
     * FlowsItem constructor.
     * @param EntityManager $em
     * @param FlowItems $flowItem
     * @param $subscriber
     * @param string $typePush
     * @param null $user_ref
     * @param null $tag
     * @param string $messageType
     */
    public function __construct(EntityManager $em, FlowItems $flowItem, $subscriber, $typePush = Message::NOTIFY_REGULAR, $user_ref = null, $tag = null, $messageType = Message::TYPE_RESPONSE)
    {
        $this->em = $em;
        $this->flowItem = $flowItem;
        $this->subscriber = $subscriber;
        $this->typePush = $typePush;
        $this->user_ref = $user_ref;
        $this->tag = $tag;
        $this->messageType = $messageType;
    }

    /**
     * Send Flow Item
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function send()
    {
        if(!empty($this->flowItem->getType()) && !empty($this->flowItem->getItems())){
            $flow = $this->flowItem->getFlow();
            if($flow instanceof Flows && $flow->getStatus() == true){
                $page = $this->em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$flow->getPageId(),'status'=>true]);
                if($page instanceof Page){
                    //Set PAGE
                    $this->setPage($page);

                    //GENERATE FLOWS ITEM OBJECT
                    $this->generateFlowsItemObject();

                    //SEND FLOW ITEM OBJECT
                    $this->sendFlowsItemObject();

                    //CHECK NEXT STEP
                    if(!is_array($this->result) || !array_key_exists('checkUserInput', $this->result) || $this->result['checkUserInput'] != false) {
                        $this->checkNextStepFlowItem();
                    }
                }
            }
        }
        if(array_key_exists('result', $this->result) && $this->result['result'] == false){
            $fs = new Filesystem();
            $fs->appendToFile('send_flow_error.txt', json_encode($this->result)."\n\n");
        }
        return $this->result;
    }

    /**
     * Generate Flows Item Object
     */
    public function generateFlowsItemObject(){
        switch ($this->flowItem->getType()){
            //TYPE TEXT
            case FlowItems::TYPE_SEND_MESSAGE:
                $sendMessageType = new SendMessageType($this->em, $this->page, $this->flowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                $this->setFlowsItemObject($sendMessageType);
                break;
            //TYPE PERFORM ACTIONS
            case FlowItems::TYPE_PERFORM_ACTIONS:
                $performActionType = new PerformActionType($this->em, $this->page, $this->flowItem, $this->subscriber);
                $this->setFlowsItemObject($performActionType);
                break;
            case FlowItems::TYPE_START_ANOTHER_FLOW:
                $startAnotherFlowType = new StartAnotherFlowType($this->em, $this->page, $this->flowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                $this->setFlowsItemObject($startAnotherFlowType);
                break;
            case FlowItems::TYPE_CONDITION:
                $conditionType = new ConditionType($this->em, $this->page, $this->flowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                $this->setFlowsItemObject($conditionType);
                break;
            case FlowItems::TYPE_RANDOMIZER:
                $randomizerType = new RandomizerType($this->em, $this->page, $this->flowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                $this->setFlowsItemObject($randomizerType);
                break;
            case FlowItems::TYPE_SMART_DELAY:
                $smartDelayType = new SmartDelayType($this->em, $this->flowItem, $this->subscriber);
                $this->setFlowsItemObject($smartDelayType);
                break;
        }
    }

    /**
     * Send FLows Item Object
     */
    public function sendFlowsItemObject(){
        if($this->flowsItemObject instanceof FlowsTypeInterface){
            $this->setResult($this->flowsItemObject->send());
        }
    }

    /**
     * Check and send next step flow item
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function checkNextStepFlowItem(){
        if($this->flowItem->getType() != FlowItems::TYPE_SMART_DELAY && !empty($this->flowItem->getNextStep())){
            $nextFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$this->flowItem->getFlow(), 'uuid'=>$this->flowItem->getNextStep()]);
            if($nextFlowItem instanceof FlowItems){
                $flowsItem = new FlowsItem($this->em, $nextFlowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                $this->setResult($flowsItem->send());
            }
        }
    }

    /**
     * @return array|mixed
     */
    public function getJSON()
    {
        $flow = $this->flowItem->getFlow();
        if($flow instanceof Flows){
            $page = $this->em->getRepository("AppBundle:Page")->findOneBy(['page_id'=>$flow->getPageId(),'status'=>true]);
            if($page instanceof Page){
                //Set PAGE
                $this->setPage($page);

                //GENERATE FLOWS ITEM OBJECT
                $this->generateFlowsItemObject();

                //GET JSON FLOW ITEM OBJECT
                $this->getJSONFlowsItemObject();
            }
            else{
                $this->setResult([
                    'result' => false,
                    'adsJSON' => "To set up the Ads JSON you need to attach at least one button or Quick Reply to an opt-in message"
                ]);
            }
        }
        else{
            $this->setResult([
                'result' => false,
                'adsJSON' => "To set up the Ads JSON you need to attach at least one button or Quick Reply to an opt-in message"
            ]);
        }

        return $this->result;
    }

    /**
     * Get Json Flow Items Object
     */
    public function getJSONFlowsItemObject(){
        if($this->flowsItemObject instanceof FlowsTypeInterface){
            $this->setResult($this->flowsItemObject->getJSON());
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
     * @return FlowsTypeInterface|null
     */
    public function getFlowsItemObject()
    {
        return $this->flowsItemObject;
    }

    /**
     * @param FlowsTypeInterface|null $flowsItemObject
     */
    public function setFlowsItemObject($flowsItemObject)
    {
        $this->flowsItemObject = $flowsItemObject;
    }

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $result
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
