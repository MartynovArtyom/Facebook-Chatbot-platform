<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubscribersTags
 *
 * @ORM\Table(name="subscribers_tags")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubscribersTagsRepository")
 */
class SubscribersTags
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
     * @ORM\ManyToOne(targetEntity="Subscribers")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $subscriber;

    /**
     * @var Tag
     *
     * @ORM\ManyToOne(targetEntity="Tag")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $tag;

    /**
     * SubscribersTags constructor.
     * @param Subscribers $subscriber
     * @param Tag $tag
     */
    public function __construct(Subscribers $subscriber, Tag $tag)
    {
        $this->subscriber = $subscriber;
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
     * @return Tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }
}
