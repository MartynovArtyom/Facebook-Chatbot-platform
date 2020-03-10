<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CommentDelay
 *
 * @ORM\Table(name="comment_delay")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommentDelayRepository")
 */
class CommentDelay
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
     * @var Widget
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Widget", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $widget;

    /**
     * @ORM\Column(type="string")
     */
    private $commentId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sendDate;

    /**
     * @ORM\Column(type="string")
     */
    private $recipient;

    /**
     * CommentDelay constructor.
     * @param Widget $widget
     * @param $commentId
     * @param $sendDate
     * @param $recipient
     * @throws \Exception
     */
    public function __construct(Widget $widget, $commentId, $sendDate, $recipient)
    {
        $this->widget = $widget;
        $this->commentId = $commentId;
        $this->sendDate = new \DateTime('+'.$sendDate.' minutes');
        $this->recipient = $recipient;
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
    public function getCommentId()
    {
        return $this->commentId;
    }

    /**
     * @param mixed $commentId
     */
    public function setCommentId($commentId)
    {
        $this->commentId = $commentId;
    }

    /**
     * @return mixed
     */
    public function getSendDate()
    {
        return $this->sendDate;
    }

    /**
     * @param mixed $sendDate
     */
    public function setSendDate($sendDate)
    {
        $this->sendDate = $sendDate;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param mixed $recipient
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }
}
