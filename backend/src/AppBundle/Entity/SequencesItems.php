<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SequencesItems
 *
 * @ORM\Table(name="sequences_items")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SequencesItemsRepository")
 */
class SequencesItems
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
     * @var Sequences
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Sequences", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $sequence;

    /**
     * @var Flows
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Flows", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $flow;

    /**
     * immediately minutes hours days
     * @ORM\Column(type="array")
     */
    private $delay;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * SequencesItems constructor.
     * @param Sequences $sequence
     * @param $number
     * @param Flows|null $flow
     * @param array $delay
     * @param bool $status
     */
    public function __construct(Sequences $sequence, $number, Flows $flow = null, $delay=['type'=>'days','value'=>1], $status=false)
    {
        $this->sequence = $sequence;
        $this->number = $number;
        $this->flow = $flow;
        $this->delay = $delay;
        $this->status = $status;
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
     * @return Sequences
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param Sequences $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
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
    public function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param mixed $delay
     */
    public function setDelay($delay)
    {
        $this->delay = $delay;
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
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param mixed $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }
}
