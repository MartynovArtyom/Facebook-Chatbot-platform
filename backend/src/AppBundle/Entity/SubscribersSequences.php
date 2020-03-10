<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubscribersSequences
 *
 * @ORM\Table(name="subscribers_sequences")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubscribersSequencesRepository")
 */
class SubscribersSequences
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
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $subscriber;

    /**
     * @var Sequences
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Sequences", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $sequence;

    /**
     * @ORM\Column(type="string")
     */
    private $stage;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastSendDate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $processed;

    /**
     * SubscribersSequences constructor.
     * @param Subscribers $subscriber
     * @param Sequences $sequence
     * @param int $stage
     * @param bool $processed
     */
    public function __construct(Subscribers $subscriber, Sequences $sequence, $stage = 1, $processed = false)
    {
        $this->subscriber = $subscriber;
        $this->sequence = $sequence;
        $this->stage = $stage;
        $this->processed = $processed;
        $this->lastSendDate = new \DateTime();
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
     * @return mixed
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * @param mixed $stage
     */
    public function setStage($stage)
    {
        $this->stage = $stage;
    }

    /**
     * @return mixed
     */
    public function getLastSendDate()
    {
        return $this->lastSendDate;
    }

    /**
     * @param mixed $lastSendDate
     */
    public function setLastSendDate($lastSendDate)
    {
        $this->lastSendDate = $lastSendDate;
    }

    /**
     * @return mixed
     */
    public function getProcessed()
    {
        return $this->processed;
    }

    /**
     * @param mixed $processed
     */
    public function setProcessed($processed)
    {
        $this->processed = $processed;
    }
}
