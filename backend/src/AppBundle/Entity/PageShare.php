<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PageShare
 *
 * @ORM\Table(name="page_share")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PageShareRepository")
 */
class PageShare
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
     *     message="page_id is required",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="string")
     */
    private $page_id;

    /**
     * @Assert\NotBlank(
     *     message="token is required",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="string")
     */
    private $token;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="status should be boolean type",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="widgets should be boolean type",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $widgets;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="sequences should be boolean type",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $sequences;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="keywords should be boolean type",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $keywords;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="welcomeMessage should be boolean type",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $welcomeMessage;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="defaultReply should be boolean type",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $defaultReply;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="mainMenu should be boolean type",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $mainMenu;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="flows should be boolean type",
     *     groups={"pageShare"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $flows;

    /**
     * PageShare constructor.
     * @param $page_id
     * @throws \Exception
     */
    public function __construct($page_id) {
        $this->page_id = $page_id;
        $this->token = bin2hex(random_bytes(16));
        $this->status = false;

        $this->widgets = true;
        $this->sequences = true;
        $this->keywords = true;
        $this->welcomeMessage = true;
        $this->defaultReply = true;
        $this->mainMenu = true;
        $this->flows = true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
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
     * @return mixed
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @param mixed $widgets
     */
    public function setWidgets($widgets)
    {
        $this->widgets = $widgets;
    }

    /**
     * @return mixed
     */
    public function getSequences()
    {
        return $this->sequences;
    }

    /**
     * @param mixed $sequences
     */
    public function setSequences($sequences)
    {
        $this->sequences = $sequences;
    }

    /**
     * @return mixed
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * @param mixed $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    /**
     * @return mixed
     */
    public function getWelcomeMessage()
    {
        return $this->welcomeMessage;
    }

    /**
     * @param mixed $welcomeMessage
     */
    public function setWelcomeMessage($welcomeMessage)
    {
        $this->welcomeMessage = $welcomeMessage;
    }

    /**
     * @return mixed
     */
    public function getDefaultReply()
    {
        return $this->defaultReply;
    }

    /**
     * @param mixed $defaultReply
     */
    public function setDefaultReply($defaultReply)
    {
        $this->defaultReply = $defaultReply;
    }

    /**
     * @return mixed
     */
    public function getMainMenu()
    {
        return $this->mainMenu;
    }

    /**
     * @param mixed $mainMenu
     */
    public function setMainMenu($mainMenu)
    {
        $this->mainMenu = $mainMenu;
    }

    /**
     * @return mixed
     */
    public function getFlows()
    {
        return $this->flows;
    }

    /**
     * @param mixed $flows
     */
    public function setFlows($flows)
    {
        $this->flows = $flows;
    }

    /**
     * @return array
     */
    public function toArray(){
        return [
            'id' => $this->id,
            'page_id' => $this->page_id,
            'token' => $this->token,
            'status' => $this->status,
            'widgets' => $this->widgets,
            'sequences' => $this->sequences,
            'keywords' => $this->keywords,
            'welcomeMessage' => $this->welcomeMessage,
            'defaultReply' => $this->defaultReply,
            'mainMenu' => $this->mainMenu,
            'flows' => $this->flows
        ];
    }

    /**
     * @param array $params
     */
    public function update(array $params){
        foreach($params as $key => $value) {
            if (property_exists($this, $key) && $key != 'id' && $key != 'page_id' && $key != 'token') {
                $this->$key = $value;
            }
        }
    }
}
