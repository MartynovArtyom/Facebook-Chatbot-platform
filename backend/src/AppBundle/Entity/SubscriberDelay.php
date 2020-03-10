<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubscriberDelay
 *
 * @ORM\Table(name="subscriber_delay")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubscriberDelayRepository")
 */
class SubscriberDelay
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Subscribers
     *
     * @ORM\ManyToOne(targetEntity="Subscribers", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $subscriber;

    /**
     * @var FlowItems
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\FlowItems", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $flowItem;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sendDate;

    /**
     * SubscriberDelay constructor.
     * @param Subscribers $subscriber
     * @param FlowItems $flowItem
     * @param $sendDate
     */
    public function __construct(Subscribers $subscriber, FlowItems $flowItem, $sendDate)
    {
        $this->subscriber = $subscriber;
        $this->flowItem = $flowItem;
        $this->sendDate = $sendDate;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Subscribers
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * @param Subscribers $subscriber
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;
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
     * @return mixed
     */
    public function getSendDate()
    {
        return $this->sendDate;
    }

    /**
     * @param mixed $sendDate
     */
    public function setSendDate($sendDate)
    {
        $this->sendDate = $sendDate;
    }
}
