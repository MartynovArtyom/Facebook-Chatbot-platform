<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DefaultReply
 *
 * @ORM\Table(name="default_reply")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DefaultReplyRepository")
 */
class DefaultReply
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
     * @var Flows
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Flows", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $flow;

    /**
     * @ORM\Column(type="boolean", options={"default":true})
     */
    private $status;

    /**
     * 1 = Once per 24h,
     * 2 = Every time
     * @ORM\Column(type="integer", options={"default":1})
     */
    private $type;

    /**
     * DefaultReply constructor.
     * @param $page_id
     * @param Flows $flow
     * @param $status
     * @param $type
     */
    public function __construct($page_id, Flows $flow, $status=true, $type=1)
    {
        $this->page_id = $page_id;
        $this->flow = $flow;
        $this->status = $status;
        $this->type = $type;
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
}
