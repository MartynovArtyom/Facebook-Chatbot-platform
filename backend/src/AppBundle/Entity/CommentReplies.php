<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommentReplies
 *
 * @ORM\Table(name="comment_replies")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommentRepliesRepository")
 */
class CommentReplies
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
     * @var Widget
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Widget", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $widget;

    /**
     * @ORM\Column(type="string")
     */
    private $messageId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $subscriberId;

    /**
     * CommentReplies constructor.
     * @param Widget $widget
     * @param $page_id
     * @param $messageId
     * @param $subscriberId
     */
    public function __construct(Widget $widget, $page_id, $messageId, $subscriberId=null)
    {
        $this->widget = $widget;
        $this->page_id = $page_id;
        $this->messageId = $messageId;
        $this->subscriberId = $subscriberId;
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

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @param mixed $messageId
     */
    public function setMessageId($messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * @return mixed
     */
    public function getSubscriberId()
    {
        return $this->subscriberId;
    }

    /**
     * @param mixed $subscriberId
     */
    public function setSubscriberId($subscriberId)
    {
        $this->subscriberId = $subscriberId;
    }
}
