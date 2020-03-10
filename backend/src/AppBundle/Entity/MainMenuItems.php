<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MainMenuItems
 *
 * @ORM\Table(name="main_menu_items")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MainMenuItemsRepository")
 */
class MainMenuItems
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
     * @var MainMenu
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MainMenu", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $mainMenu;

    /**
     * @ORM\Column(type="string")
     */
    private $uuid;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $type;
    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @var Flows
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Flows", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $flow;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $actions;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $parentID;

    /**
     * @ORM\Column(type="integer")
     */
    private $clicked;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $viewSize;

    /**
     * @ORM\Column(type="boolean", options={"default":1},)
     */
    private $removed;

    /**
     * MainMenuItems constructor.
     * @param MainMenu $mainMenu
     * @param $uuid
     * @param $name
     * @param $type
     * @param $position
     * @param $flow
     * @param $actions
     * @param $url
     * @param $parentID
     * @param $viewSize
     * @param $removed
     */
    public function __construct(MainMenu $mainMenu, $uuid, $name, $type, $position, $flow, $actions, $url, $parentID, $viewSize=null, $removed=true)
    {
        $this->mainMenu = $mainMenu;
        $this->uuid = $uuid;
        $this->name = json_encode($name);
        $this->type = $type;
        $this->position = $position;
        $this->flow = $flow;
        $this->actions = json_encode($actions);
        $this->url = $url;
        $this->parentID = $parentID;
        $this->viewSize = $viewSize;
        $this->clicked = 0;
        $this->removed = $removed;
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
     * @return MainMenu
     */
    public function getMainMenu()
    {
        return $this->mainMenu;
    }

    /**
     * @param MainMenu $mainMenu
     */
    public function setMainMenu($mainMenu)
    {
        $this->mainMenu = $mainMenu;
    }

    /**
     * @return mixed
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param mixed $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
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
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
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
    public function getActions()
    {
        return json_decode($this->actions, true);
    }

    /**
     * @param mixed $actions
     */
    public function setActions($actions)
    {
        $this->actions = json_encode($actions);
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return mixed
     */
    public function getParentID()
    {
        return $this->parentID;
    }

    /**
     * @param mixed $parentID
     */
    public function setParentID($parentID)
    {
        $this->parentID = $parentID;
    }

    /**
     * @return mixed
     */
    public function getClicked()
    {
        return $this->clicked;
    }

    /**
     * @param mixed $clicked
     */
    public function setClicked($clicked)
    {
        $this->clicked = $clicked;
    }

    /**
     * @return mixed
     */
    public function getViewSize()
    {
        return $this->viewSize;
    }

    /**
     * @param mixed $viewSize
     */
    public function setViewSize($viewSize)
    {
        $this->viewSize = $viewSize;
    }

    /**
     * @return mixed
     */
    public function getRemoved()
    {
        return $this->removed;
    }

    /**
     * @param mixed $removed
     */
    public function setRemoved($removed)
    {
        $this->removed = $removed;
    }

    /**
     * @param $name
     * @param $type
     * @param $position
     * @param $flow
     * @param $actions
     * @param $url
     * @param $parentID
     * @param $viewSize
     * @param $removed
     */
    public function update($name, $type, $position, $flow, $actions, $url, $parentID, $viewSize, $removed)
    {
        $this->name = json_encode($name);
        $this->type = $type;
        $this->position = $position;
        $this->flow = $flow;
        $this->actions = json_encode($actions);
        $this->url = $url;
        $this->parentID = $parentID;
        $this->viewSize = $viewSize;
        $this->removed = $removed;
    }

}
