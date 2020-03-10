<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 17:00
 */

namespace AppBundle\Flows\Type;
use AppBundle\Entity\FlowItems;
use AppBundle\Entity\SubscriberDelay;
use AppBundle\Entity\Subscribers;
use Doctrine\ORM\EntityManager;


/**
 * Class SmartDelayType
 * @package AppBundle\Flows\Type
 */
class SmartDelayType implements FlowsTypeInterface
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
     * SmartDelayType constructor.
     * @param EntityManager $em
     * @param FlowItems $flowItem
     * @param Subscribers|string $subscriber
     */
    public function __construct(EntityManager $em, FlowItems $flowItem, $subscriber)
    {
        $this->em = $em;
        $this->flowItem = $flowItem;
        $this->subscriber = $subscriber;
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function send()
    {
        $result = false;
        if($this->subscriber instanceof Subscribers){
            if(!empty($this->flowItem->getItems())) {
                if (isset($this->flowItem->getItems()[0]) && !empty($this->flowItem->getItems()[0])) {
                    $item = $this->flowItem->getItems()[0];
                    if(isset($item['time']) && $item['time'] > 0 && isset($item['type_action']) && in_array($item['type_action'], [1,2,3])){
                        if(!empty($this->flowItem->getNextStep())){
                            $nextFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$this->flowItem->getFlow(), 'uuid'=>$this->flowItem->getNextStep()]);
                            if($nextFlowItem instanceof FlowItems){
                                if($item['type_action'] == 1){
                                    $delayType = 'minutes';
                                }
                                elseif ($item['type_action'] == 2){
                                    $delayType = 'hours';
                                }
                                else{
                                    $delayType = 'days';
                                }
                                $sendDate = new \DateTime('+'.$item['time'].' '.$delayType);
                                if($sendDate instanceof \DateTime){
                                    $subscriberDelay = new SubscriberDelay($this->subscriber, $nextFlowItem, $sendDate);
                                    $this->em->persist($subscriberDelay);
                                    $this->em->flush();

                                    $result = true;
                                }
                            }
                        }
                    }
                }
            }
        }
        return ['result' => $result];
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
}
