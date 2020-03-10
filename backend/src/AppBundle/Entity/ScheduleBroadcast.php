<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ScheduleBroadcast
 *
 * @ORM\Table(name="schedule_broadcast")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ScheduleBroadcastRepository")
 */
class ScheduleBroadcast
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
     * @var Broadcast
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Broadcast")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $broadcast;

    /**
     * @var Subscribers
     *
     * @ORM\ManyToOne(targetEntity="Subscribers")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $subscriber;

    /**
     * ScheduleBroadcast constructor.
     * @param Broadcast $broadcast
     * @param Subscribers $subscriber
     */
    public function __construct(Broadcast $broadcast, Subscribers $subscriber)
    {
        $this->broadcast = $broadcast;
        $this->subscriber = $subscriber;
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
     * @return Broadcast
     */
    public function getBroadcast()
    {
        return $this->broadcast;
    }

    /**
     * @param Broadcast $broadcast
     */
    public function setBroadcast($broadcast)
    {
        $this->broadcast = $broadcast;
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
}
