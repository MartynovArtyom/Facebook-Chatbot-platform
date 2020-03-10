<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Flows
 *
 * @ORM\Table(name="flows")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FlowsRepository")
 */
class Flows
{
    const FLOW_TYPE_CONTENT = 1;
    const FLOW_TYPE_DEFAULT_REPLY = 2;
    const FLOW_TYPE_WELCOME_MESSAGE = 3;
    const FLOW_TYPE_KEYWORDS = 4;
    const FLOW_TYPE_SEQUENCES = 5;
    const FLOW_TYPE_MENU = 6;
    const FLOW_TYPE_WIDGET = 7;
    const FLOW_TYPE_BROADCAST = 8;

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
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     * 1 = content
     * 2 = default_reply
     * 3 = welcome_message
     * 4 = keywords
     * 5 = sequences
     * 6 = menu
     * 7 = widget
     * 8 = broadcast
     */
    private $type;

    /**
     * @var Folders
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Folders", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $folder;

    /**
     * @ORM\Column(type="datetime")
     */
    private $modified;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * Flows constructor.
     * @param $page_id
     * @param $name
     * @param int $type
     * @param Folders|null $folder
     * @param bool $status
     * @throws \Exception
     */
    public function __construct($page_id, $name, $type=self::FLOW_TYPE_CONTENT, Folders $folder=null, $status=true)
    {
        $this->page_id = $page_id;
        $this->name = json_encode($name);
        $this->type = $type;
        $this->folder = $folder;
        $this->modified = new \DateTime();
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
     * @return Folders
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param Folders $folder
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }

    /**
     * @return mixed
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param mixed $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
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

}
