<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 16:23
 */

namespace AppBundle\Flows\Type;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Helper\Subscriber\SubscriberActionHelper;
use Doctrine\ORM\EntityManager;

/**
 * Class PerformActionType
 * @package AppBundle\Flows\Type
 */
class PerformActionType implements FlowsTypeInterface
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
     * PerformActionType constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param Subscribers|string $subscriber
     */
    public function __construct(EntityManager $em, Page $page, FlowItems $flowItem, $subscriber)
    {
        $this->em = $em;
        $this->page = $page;
        $this->flowItem = $flowItem;
        $this->subscriber = $subscriber;
    }

    /**
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function send(){
        if($this->getSubscriber() instanceof Subscribers){
            SubscriberActionHelper::addActionForSubscriber($this->em, $this->page, $this->flowItem->getItems(), $this->subscriber);
        }

        return ['result' => true];
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
}
