<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserInputDelay
 *
 * @ORM\Table(name="user_input_delay")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserInputDelayRepository")
 */
class UserInputDelay
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
     * @ORM\Column(type="string")
     */
    private $page_id;

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
     * @ORM\Column(type="string")
     */
    private $itemUuid;

    /**
     * UserInputDelay constructor.
     * @param $page_id
     * @param Subscribers $subscriber
     * @param FlowItems $flowItem
     * @param $itemUuid
     */
    public function __construct($page_id, Subscribers $subscriber, FlowItems $flowItem, $itemUuid)
    {
        $this->page_id = $page_id;
        $this->subscriber = $subscriber;
        $this->flowItem = $flowItem;
        $this->itemUuid = $itemUuid;
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
     * @return mixed
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * @param mixed $page_id
     */
    public function setPageId($page_id)
    {
        $this->page_id = $page_id;
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
    public function getItemUuid()
    {
        return $this->itemUuid;
    }

    /**
     * @param mixed $itemUuid
     */
    public function setItemUuid($itemUuid)
    {
        $this->itemUuid = $itemUuid;
    }
}
