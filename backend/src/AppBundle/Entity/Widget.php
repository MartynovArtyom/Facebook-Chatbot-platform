<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Widget
 *
 * @ORM\Table(name="widget")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\WidgetRepository")
 */
class Widget
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
     * @var Flows
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Flows", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $flow;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * 1 = Bar
     * 2 = SlideIn
     * 3 = Modal
     * 4 = Page Takeover
     * 5 = Button
     * 6 = Box
     * 7 = Ref Url
     * 8 = Ads JSON
     * 9 = Messenger Code
     * 10 = Customer Chat
     * 11 = Comments
     * 12 = Autoresponder
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $options;

    /**
     * @var Sequences
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Sequences", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $sequence;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $shows;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $optIn;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $postId;

    /**
     * String Type
     * @var array
     */
    public $stringType = [
        1 => 'bar',
        2 => 'slidein',
        3 => 'modal',
        4 => 'page_takeover',
        5 => 'button',
        6 => 'box',
        10 => 'chat',
    ];

    /**
     * Widget constructor.
     * @param $page_id
     * @param Flows $flow
     * @param $name
     * @param $type
     * @param array $options
     * @param Sequences|null $sequence
     * @param null $postId
     */
    public function __construct($page_id, Flows $flow, $name, $type, $options=[], Sequences $sequence=null, $postId=null)
    {
        $this->page_id = $page_id;
        $this->flow = $flow;
        $this->name = json_encode($name);
        $this->type = $type;
        $this->options = json_encode($options);
        $this->sequence = $sequence;
        $this->postId = $postId;
        if($type == 8 || $type == 12){
            $this->status = true;
        }
        else{
            $this->status = false;
        }
        $this->shows = 0;
        $this->optIn = 0;
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
    public function getName()
    {
        if(json_decode($this->name, true)){
            return json_decode($this->name, true);
        }
        else{
            return $this->name;
        }
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = json_encode($name);
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
    public function getOptions()
    {
        return json_decode($this->options, true);
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = json_encode($options);
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
     * @return int
     */
    public function getShows()
    {
        return $this->shows;
    }

    /**
     * @param int $shows
     */
    public function setShows($shows)
    {
        $this->shows = $shows;
    }

    /**
     * @return int
     */
    public function getOptIn()
    {
        return $this->optIn;
    }

    /**
     * @param int $optIn
     */
    public function setOptIn($optIn)
    {
        $this->optIn = $optIn;
    }

    /**
     * @return mixed
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * @param mixed $postId
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;
    }

    /**
     * @return mixed
     */
    public function getStringType(){
        return (isset($this->stringType[$this->type])) ? $this->stringType[$this->type] : null;
    }
}
