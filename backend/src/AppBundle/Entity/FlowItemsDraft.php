<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FlowItemsDraft
 *
 * @ORM\Table(name="flow_items_draft")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FlowItemsDraftRepository")
 */
class FlowItemsDraft
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
     * @var Flows
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Flows", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $flow;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $items;

    /**
     * FlowItemsDraft constructor.
     * @param Flows $flow
     * @param $items
     */
    public function __construct(Flows $flow, $items)
    {
        $this->flow = $flow;
        $this->items = json_encode($items);
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
}
