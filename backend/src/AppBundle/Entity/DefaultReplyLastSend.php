<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DefaultReplyLastSend
 *
 * @ORM\Table(name="default_reply_last_send")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DefaultReplyLastSendRepository")
 */
class DefaultReplyLastSend
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
     * @var DefaultReply
     *
     * @ORM\ManyToOne(targetEntity="DefaultReply")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $defaultReply;

    /**
     * @var Subscribers
     *
     * @ORM\ManyToOne(targetEntity="Subscribers", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $subscriber;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastSend;

    /**
     * DefaultReplyLastSend constructor.
     * @param DefaultReply $defaultReply
     * @param Subscribers $subscriber
     */
    public function __construct(DefaultReply $defaultReply, Subscribers $subscriber)
    {
        $this->defaultReply = $defaultReply;
        $this->subscriber = $subscriber;
        $this->lastSend = new \DateTime();
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
     * @return DefaultReply
     */
    public function getDefaultReply()
    {
        return $this->defaultReply;
    }

    /**
     * @param DefaultReply $defaultReply
     */
    public function setDefaultReply($defaultReply)
    {
        $this->defaultReply = $defaultReply;
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
     * @return mixed
     */
    public function getLastSend()
    {
        return $this->lastSend;
    }

    /**
     * @param mixed $lastSend
     */
    public function setLastSend($lastSend)
    {
        $this->lastSend = $lastSend;
    }
}
