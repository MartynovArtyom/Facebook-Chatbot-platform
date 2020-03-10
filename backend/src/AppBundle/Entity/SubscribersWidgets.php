<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubscribersWidgets
 *
 * @ORM\Table(name="subscribers_widgets")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubscribersWidgetsRepository")
 */
class SubscribersWidgets
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
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $subscriber;

    /**
     * @var Widget
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Widget", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $widget;

    /**
     * SubscribersWidgets constructor.
     * @param Subscribers $subscriber
     * @param Widget $widget
     */
    public function __construct(Subscribers $subscriber, Widget $widget)
    {
        $this->subscriber = $subscriber;
        $this->widget = $widget;
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
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param Widget $widget
     */
    public function setWidget($widget)
    {
        $this->widget = $widget;
    }
}
