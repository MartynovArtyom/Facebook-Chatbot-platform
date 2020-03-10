<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConversationMessages
 *
 * @ORM\Table(name="conversation_messages")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ConversationMessagesRepository")
 */
class ConversationMessages
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
     * @var Conversation
     *
     * @ORM\ManyToOne(targetEntity="Conversation")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $conversation;

    /**
     * @ORM\Column(type="text")
     */
    private $items;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="integer")
     *
     * 1=user
     * 2=page
     */
    private $type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * ConversationMessages constructor.
     * @param Conversation $conversation
     * @param $items
     * @param $type
     * @param null $text
     * @throws \Exception
     */
    public function __construct(Conversation $conversation, $items, $type, $text=null)
    {
        $this->conversation = $conversation;
        $this->items = json_encode($items);
        $this->text = $text;
        $this->type = $type;
        $this->created = new \DateTime();
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
     * @return Conversation
     */
    public function getConversation()
    {
        return $this->conversation;
    }

    /**
     * @param Conversation $conversation
     */
    public function setConversation($conversation)
    {
        $this->conversation = $conversation;
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

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
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
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }
}
