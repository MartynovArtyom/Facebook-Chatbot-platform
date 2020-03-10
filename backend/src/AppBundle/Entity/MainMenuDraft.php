<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MainMenuDraft
 *
 * @ORM\Table(name="main_menu_draft")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MainMenuDraftRepository")
 */
class MainMenuDraft
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
     * @ORM\Column(type="text")
     */
    private $items;

    /**
     * MainMenuDraft constructor.
     * @param MainMenu $mainMenu
     * @param $items
     */
    public function __construct(MainMenu $mainMenu, $items)
    {
        $this->mainMenu = $mainMenu;
        $this->items = json_encode($items);
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

}
