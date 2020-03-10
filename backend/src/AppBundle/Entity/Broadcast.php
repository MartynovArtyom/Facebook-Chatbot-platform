<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Broadcast
 *
 * @ORM\Table(name="broadcast")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BroadcastRepository")
 */
class Broadcast
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
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var Flows
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Flows", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $flow;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $targeting;

    /**
     * 1 = Subscription
     * 2 = Promotional
     * 3 = Follow-Up
     *
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * 1=Regular Push
     * 2=Silent Push
     * 3=Silent
     *
     * @ORM\Column(type="integer")
     */
    private $pushType;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * 1=draft
     * 2=schedule
     * 3=history
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $tag;

    /**
     * Broadcast constructor.
     * @param $page_id
     * @param $name
     * @param Flows $flow
     * @param array $targeting
     * @param int $type
     * @param int $pushType
     * @param int $status
     * @param null $tag
     * @throws \Exception
     */
    public function __construct($page_id, $name, Flows $flow, $targeting=[], $type=2, $pushType=1, $status=1, $tag=null)
    {
        $this->page_id = $page_id;
        $this->name = json_encode($name);
        $this->flow = $flow;
        $this->targeting = json_encode($targeting);
        $this->type = $type;
        $this->pushType = $pushType;
        $this->status = $status;
        $this->created = new \DateTime();
        $this->tag = $tag;
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
    public function getTargeting()
    {
        return json_decode($this->targeting, true);
    }

    /**
     * @param mixed $targeting
     */
    public function setTargeting($targeting)
    {
        $this->targeting = json_encode($targeting);
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
    public function getPushType()
    {
        return $this->pushType;
    }

    /**
     * @param mixed $pushType
     */
    public function setPushType($pushType)
    {
        $this->pushType = $pushType;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param mixed $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @param $pushType
     * @param \DateTime $created
     * @param $status
     * @param array $targeting
     * @param int $type
     * @param null $tag
     */
    public function update($pushType, \DateTime $created, $status, $targeting=[], $type=2, $tag=null){
        $this->pushType = $pushType;
        $this->created = $created;
        $this->status = $status;
        $this->targeting = json_encode($targeting);
        $this->type = $type;
        $this->tag = $tag;
    }
}
