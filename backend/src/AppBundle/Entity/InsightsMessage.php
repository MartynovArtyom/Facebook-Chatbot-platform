<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InsightsMessage
 *
 * @ORM\Table(name="insights_message")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InsightsMessageRepository")
 */
class InsightsMessage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var FlowItems
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\FlowItems", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $flowItem;

    /**
     * @ORM\Column(type="string")
     */
    private $recipient;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $watermark;

    /**
     * @ORM\Column(type="boolean")
     */
    private $delivery;

    /**
     * InsightsMessage constructor.
     * @param FlowItems $flowItem
     * @param $recipient
     * @param null $watermark
     * @param bool $delivery
     */
    public function __construct(FlowItems $flowItem, $recipient, $watermark=NULL, $delivery=false)
    {
        $this->flowItem = $flowItem;
        $this->recipient = $recipient;
        $this->watermark = $watermark;
        $this->delivery = $delivery;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param mixed $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }


    /**
     * @return mixed
     */
    public function getWatermark()
    {
        return $this->watermark;
    }

    /**
     * @param mixed $watermark
     */
    public function setWatermark($watermark)
    {
        $this->watermark = $watermark;
    }

    /**
     * @return mixed
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param mixed $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }
}
