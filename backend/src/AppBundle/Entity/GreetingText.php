<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GreetingText
 *
 * @ORM\Table(name="greeting_text")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GreetingTextRepository")
 */
class GreetingText
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
     * @Assert\NotBlank(
     *     message="page_id should not be empty",
     *     groups={"greetingText"}
     * )
     * @ORM\Column(type="string")
     */
    private $page_id;

    /**
     * @Assert\NotBlank(
     *     message="Should be not empty",
     *     groups={"greetingText"}
     * )
     * @Assert\Length(
     *     max="160",
     *     maxMessage="Max 160 symbols",
     *     groups={"greetingText"}
     * )
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * GreetingText constructor.
     * @param $page_id
     * @param $text
     */
    public function __construct($page_id, $text)
    {
        $this->page_id = $page_id;
        $this->text = json_encode($text);
    }

    /**
     * Get id
     *
     * @return int
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
    public function getText()
    {
        return json_decode($this->text, true);
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = json_encode($text);
    }

}

