<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 16:48
 */

namespace AppBundle\Flows\Type;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Flows\FlowsItem;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;

/**
 * Class ConditionType
 * @package AppBundle\Flows\Type
 */
class ConditionType implements FlowsTypeInterface
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
     * ConditionType constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param $subscriber
     * @param string $typePush
     * @param null $user_ref
     * @param null $tag
     * @param string $messageType
     */
    public function __construct(EntityManager $em, Page $page, FlowItems $flowItem, $subscriber, $typePush = Message::NOTIFY_REGULAR, $user_ref = null, $tag = null, $messageType = Message::TYPE_RESPONSE)
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
                if(isset($item['conditions']) && !empty($item['conditions'])
                    && ((isset($item['invalid_step']) && !empty($item['invalid_step'])) || (isset($item['valid_step']) && !empty($item['valid_step'])))
                ){
                    $validNextStepFlowItem = null;
                    $inValidNextStepFlowItem = null;
                    if(isset($item['valid_step']['next_step']) && !empty($item['valid_step']['next_step'])){
                        $validNextStepFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$this->flowItem->getFlow(), 'uuid'=>$item['valid_step']['next_step']]);
                    }
                    if(isset($item['invalid_step']['next_step']) && !empty($item['invalid_step']['next_step'])){
                        $inValidNextStepFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$this->flowItem->getFlow(), 'uuid'=>$item['invalid_step']['next_step']]);
                    }
                    if($validNextStepFlowItem instanceof FlowItems || $inValidNextStepFlowItem instanceof FlowItems){
                        if($this->subscriber instanceof Subscribers){
                            $conditions = [
                                'system' => [],
                                'tags' => [],
                                'widgets' => [],
                                'sequences' => [],
                                'customFields' => []
                            ];
                            if(!empty($item['conditions'])){
                                foreach ($item['conditions'] as $condition){
                                    if(isset($condition['conditionType']) && !empty($condition['conditionType'])){
                                        switch($condition['conditionType']){
                                            case 'tag':
                                                $conditions['tags'][] = [
                                                    'criteria' => $condition['criteria'],
                                                    'tagID' => $condition['tagID']
                                                ];
                                                break;
                                            case 'widget';
                                                $conditions['widgets'][] = [
                                                    'criteria' => $condition['criteria'],
                                                    'widgetID' => $condition['widgetID']
                                                ];
                                                break;
                                            case 'sequence';
                                                $conditions['widgets'][] = [
                                                    'criteria' => $condition['criteria'],
                                                    'sequenceID' => $condition['sequenceID']
                                                ];
                                                break;
                                            case 'customField';
                                                $conditions['customFields'][] = [
                                                    'criteria' => $condition['criteria'],
                                                    'value' => $condition['value'],
                                                    'customFieldID' => $condition['customFieldID']
                                                ];
                                                break;
                                            default:
                                                $conditions['system'][] = $condition;
                                                break;
                                        }
                                    }
                                }
                            }
                            if(isset($item['following_conditions']) && $item['following_conditions'] == 1){
                                $checkCondition = $this->em->getRepository("AppBundle:Subscribers")->checkAnyConditionBySubscriberId($this->subscriber->getId(), $conditions);
                            }
                            else{
                                $checkCondition = $this->em->getRepository("AppBundle:Subscribers")->checkConditionBySubscriberIdByPageId($this->subscriber->getId(), $this->page->getPageId(), $conditions);
                            }
                            if($checkCondition == true){
                                if($validNextStepFlowItem instanceof FlowItems){
                                    $flowsItem = new FlowsItem($this->em, $validNextStepFlowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                                    return $flowsItem->send();
                                }
                                else{
                                    return ['result' => false];
                                }
                            }
                            else{
                                if($inValidNextStepFlowItem instanceof FlowItems){
                                    $flowsItem = new FlowsItem($this->em, $inValidNextStepFlowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                                    return $flowsItem->send();
                                }
                                else{
                                    return ['result' => false];
                                }
                            }
                        }
                        else{
                            if($inValidNextStepFlowItem instanceof FlowItems){
                                $flowsItem = new FlowsItem($this->em, $inValidNextStepFlowItem, $this->subscriber, $this->typePush, $this->user_ref, $this->tag, $this->messageType);
                                return $flowsItem->send();
                            }
                            else{
                                return ['result' => false];
                            }
                        }
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
