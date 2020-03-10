<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Keywords
 *
 * @ORM\Table(name="keywords")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\KeywordsRepository")
 */
class Keywords
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
     * @ORM\Column(type="text")
     */
    private $command;

    /**
     * 1 = is
     * 2 = contains
     * 3 = begins with
     * @ORM\Column(type="integer")
     */
    private $type;
    /**
     * @var Flows
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Flows", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $flow;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $actions;

    /**
     * @ORM\Column(type="boolean", options={"default":true})
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     */
    private $main;

    /**
     * Keywords constructor.
     * @param $page_id
     * @param $command
     * @param int $type
     * @param Flows|null $flow
     * @param array $actions
     * @param bool $status
     * @param bool $main
     */
    public function __construct($page_id, $command, $type=1, Flows $flow=null, $actions=[], $status=true, $main=false)
    {
        $this->page_id = $page_id;
        $this->command = $command;
        $this->type = $type;
        $this->flow = $flow;
        $this->actions = json_encode($actions);
        $this->status = $status;
        $this->main = $main;
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
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
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
    public function getActions()
    {
        return json_decode($this->actions, true);
    }

    /**
     * @param mixed $actions
     */
    public function setActions($actions)
    {
        $this->actions = json_encode($actions);
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
    public function getMain()
    {
        return $this->main;
    }

    /**
     * @param mixed $main
     */
    public function setMain($main)
    {
        $this->main = $main;
    }

}
