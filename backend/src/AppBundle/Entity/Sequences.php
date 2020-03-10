<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sequences
 *
 * @ORM\Table(name="sequences")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SequencesRepository")
 */
class Sequences
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
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * Sequences constructor.
     * @param $page_id
     * @param $title
     */
    public function __construct($page_id, $title)
    {
        $this->page_id = $page_id;
        $this->title = json_encode($title);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getTitle()
    {
        if(json_decode($this->title, true)){
            return json_decode($this->title, true);
        }
        else{
            return $this->title;
        }
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = json_encode($title);
    }
}
