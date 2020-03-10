<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FlowItems
 *
 * @ORM\Table(name="flow_items")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FlowItemsRepository")
 */
class FlowItems
{
    /**
     * TYPE_SEND_MESSAGE
     */
    const TYPE_SEND_MESSAGE = 'send_message';

    /**
     * TYPE_PERFORM_ACTIONS
     */
    const TYPE_PERFORM_ACTIONS = 'perform_actions';

    /**
     * TYPE_START_ANOTHER_FLOW
     */
    const TYPE_START_ANOTHER_FLOW = 'start_another_flow';

    /**
     * TYPE_CONDITION
     */
    const TYPE_CONDITION = 'condition';

    /**
     * TYPE_RANDOMIZER
     */
    const TYPE_RANDOMIZER = 'randomizer';

    /**
     * TYPE_SMART_DELAY
     */
    const TYPE_SMART_DELAY = 'smart_delay';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Flows
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Flows", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $flow;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $items;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $quickReply;

    /**
     * @ORM\Column(type="boolean", nullable=true))
     */
    private $startStep;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $nextStep;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $sent;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $delivered;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $opened;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $clicked;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $positionX;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $positionY;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $arrow;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $hideNextStep;

    /**
     * FlowItems constructor.
     * @param Flows $flow
     * @param $uuid
     * @param $name
     * @param $type
     * @param $items
     * @param $quickReply
     * @param $startStep
     * @param $nextStep
     * @param $positionX
     * @param $positionY
     * @param $arrow
     * @param $hideNextStep
     */
    public function __construct(Flows $flow, $uuid, $name, $type, $items, $quickReply, $startStep, $nextStep, $positionX, $positionY, $arrow, $hideNextStep=false)
    {
        $this->flow = $flow;
        $this->uuid = $uuid;
        $this->name = json_encode($name);
        $this->type = $type;
        $this->items = json_encode($items);
        $this->quickReply = json_encode($quickReply);
        $this->startStep = $startStep;
        $this->nextStep = $nextStep;
        $this->positionX = $positionX;
        $this->positionY = $positionY;
        $this->sent = 0;
        $this->delivered = 0;
        $this->opened = 0;
        $this->clicked = 0;
        $this->arrow = json_encode($arrow);
        $this->hideNextStep = $hideNextStep;
    }

    /**
     * @param $name
     * @param $type
     * @param $items
     * @param $quickReply
     * @param $startStep
     * @param $nextStep
     * @param $positionX
     * @param $positionY
     * @param $arrow
     * @param $hideNextStep
     */
    public function update($name, $type, $items, $quickReply, $startStep, $nextStep, $positionX, $positionY, $arrow, $hideNextStep=false){
        $this->name = json_encode($name);
        $this->type = $type;
        $this->items = json_encode($items);
        $this->quickReply = json_encode($quickReply);
        $this->startStep = $startStep;
        $this->nextStep = $nextStep;
        $this->positionX = $positionX;
        $this->positionY = $positionY;
        $this->arrow = json_encode($arrow);
        $this->hideNextStep = $hideNextStep;
    }

    /**
     * @return array
     */
    public function getResponse(){
        return [
            'id' => $this->getId(),
            'uuid' => $this->getUuid(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'widget_content' => $this->getItems(),
            'quick_reply' => $this->getQuickReply(),
            'start_step' => $this->getStartStep(),
            'next_step' => $this->getNextStep(),
            'x' => $this->getPositionX(),
            'y' => $this->getPositionY(),
            'arrow' => $this->getArrow(),
            'sent' => $this->getSent(),
            'delivered' => $this->getDelivered(),
            'opened' => $this->getOpened(),
            'clicked' => $this->getClicked(),
            'hideNextStep' => $this->getHideNextStep(),
        ];
    }

    /**
     * @return array
     */
    public function getResponseNullableStats(){
        return [
            'id' => $this->getId(),
            'uuid' => $this->getUuid(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'widget_content' => $this->getItems(),
            'quick_reply' => $this->getQuickReply(),
            'start_step' => $this->getStartStep(),
            'next_step' => $this->getNextStep(),
            'x' => $this->getPositionX(),
            'y' => $this->getPositionY(),
            'arrow' => $this->getArrow(),
            'sent' => 0,
            'delivered' => 0,
            'opened' => 0,
            'clicked' => 0,
            'hideNextStep' => $this->getHideNextStep(),
        ];
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
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        if(json_decode($this->name, true)){
            return json_decode($this->name, true);
        }
        else{
            return $this->name;
        }
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = json_encode($name);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getItems()
    {
        return json_decode($this->items, true);
    }

    /**
     * @param mixed $items
     */
    public function setItems($items)
    {
        $this->items = json_encode($items);
    }

    /**
     * @return mixed
     */
    public function getQuickReply()
    {
        return json_decode($this->quickReply, true);
    }

    /**
     * @param mixed $quickReply
     */
    public function setQuickReply($quickReply)
    {
        $this->quickReply = json_encode($quickReply);
    }

    /**
     * @return mixed
     */
    public function getStartStep()
    {
        return $this->startStep;
    }

    /**
     * @param mixed $startStep
     */
    public function setStartStep($startStep)
    {
        $this->startStep = $startStep;
    }

    /**
     * @return mixed
     */
    public function getNextStep()
    {
        return $this->nextStep;
    }

    /**
     * @param mixed $nextStep
     */
    public function setNextStep($nextStep)
    {
        $this->nextStep = $nextStep;
    }

    /**
     * @return mixed
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * @param mixed $sent
     */
    public function setSent($sent)
    {
        $this->sent = $sent;
    }

    /**
     * @return mixed
     */
    public function getDelivered()
    {
        return $this->delivered;
    }

    /**
     * @param mixed $delivered
     */
    public function setDelivered($delivered)
    {
        $this->delivered = $delivered;
    }

    /**
     * @return mixed
     */
    public function getOpened()
    {
        return $this->opened;
    }

    /**
     * @param mixed $opened
     */
    public function setOpened($opened)
    {
        $this->opened = $opened;
    }

    /**
     * @return mixed
     */
    public function getClicked()
    {
        return $this->clicked;
    }

    /**
     * @param mixed $clicked
     */
    public function setClicked($clicked)
    {
        $this->clicked = $clicked;
    }

    /**
     * @return mixed
     */
    public function getPositionX()
    {
        return $this->positionX;
    }

    /**
     * @param mixed $positionX
     */
    public function setPositionX($positionX)
    {
        $this->positionX = $positionX;
    }

    /**
     * @return mixed
     */
    public function getPositionY()
    {
        return $this->positionY;
    }

    /**
     * @param mixed $positionY
     */
    public function setPositionY($positionY)
    {
        $this->positionY = $positionY;
    }

    /**
     * @return mixed
     */
    public function getArrow()
    {
        return json_decode($this->arrow, true);
    }

    /**
     * @param mixed $arrow
     */
    public function setArrow($arrow)
    {
        $this->arrow = json_encode($arrow);
    }

    /**
     * @return mixed
     */
    public function getHideNextStep()
    {
        return $this->hideNextStep;
    }

    /**
     * @param mixed $hideNextStep
     */
    public function setHideNextStep($hideNextStep)
    {
        $this->hideNextStep = $hideNextStep;
    }
}
