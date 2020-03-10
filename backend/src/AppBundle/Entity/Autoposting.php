<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Autoposting
 *
 * @ORM\Table(name="autoposting")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AutopostingRepository")
 */
class Autoposting
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
     * @var string
     * @Assert\NotBlank(
     *     message="page_id should not be empty",
     *     groups={"autoposting"}
     * )
     * @ORM\Column(type="string")
     */
    private $page_id;

    /**
     * @var string
     * @Assert\NotBlank(
     *     message="title should not be empty",
     *     groups={"autoposting"}
     * )
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var int
     * @Assert\NotBlank(
     *     message="type should not be empty",
     *     groups={"autoposting"}
     * )
     * @Assert\GreaterThan(
     *     value="0",
     *     message="type is invalid value",
     *     groups={"autoposting"}
     * )
     * @Assert\LessThan(
     *     value="5",
     *     message="type is invalid value",
     *     groups={"autoposting"}
     * )
     * 1=RSS
     * 2=Youtube
     * 3=Twitter
     * 4=Facebook
     *
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @Assert\NotBlank(
     *     message="account should not be empty",
     *     groups={"autoposting"}
     * )
     * @ORM\Column(type="string")
     */
    private $account;

    /**
     * @Assert\NotBlank(
     *     message="url should not be empty",
     *     groups={"autoposting"}
     * )
     * @ORM\Column(type="string")
     */
    private $url;

    /**
     * @var integer
     * 1=Regular Push
     * 2=Silent Push
     * 3=Silent
     *
     * @Assert\NotBlank(
     *     message="typePush should not be empty",
     *     groups={"autoposting"}
     * )
     * @Assert\GreaterThan(
     *     value="0",
     *     message="typePush is invalid value",
     *     groups={"autoposting"}
     * )
     * @Assert\LessThan(
     *     value="4",
     *     message="typePush is invalid value",
     *     groups={"autoposting"}
     * )
     * @ORM\Column(type="integer")
     */
    private $typePush;

    /**
     * @var array
     *
     * @ORM\Column(type="array", nullable=true)
     */
    private $targeting;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $lastSeen;

    /**
     * Autoposting constructor.
     * @param $page_id
     * @param $title
     * @param $type
     * @param $account
     * @param $url
     * @param $typePush
     * @param $targeting
     * @param bool $status
     */
    public function __construct($page_id, $title, $type, $account, $url, $typePush=1, $targeting=null, $status=true)
    {
        $this->page_id = $page_id;
        $this->title = json_encode($title);
        $this->type = $type;
        $this->account = $account;
        $this->url = $url;
        $this->typePush = $typePush;
        $this->targeting = $targeting;
        $this->status = $status;
        $this->lastSeen = new \DateTime();
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
     * @return string
     */
    public function getPageId()
    {
        return $this->page_id;
    }

    /**
     * @param string $page_id
     */
    public function setPageId($page_id)
    {
        $this->page_id = $page_id;
    }

    /**
     * @return string
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
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = json_encode($title);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
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
     * @return string
     */
    public function getTypePush()
    {
        return $this->typePush;
    }

    /**
     * @param string $typePush
     */
    public function setTypePush($typePush)
    {
        $this->typePush = $typePush;
    }

    /**
     * @return array
     */
    public function getTargeting()
    {
        return $this->targeting;
    }

    /**
     * @param array $targeting
     */
    public function setTargeting($targeting)
    {
        $this->targeting = $targeting;
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getLastSeen()
    {
        return $this->lastSeen;
    }

    /**
     * @param \DateTime $lastSeen
     */
    public function setLastSeen($lastSeen)
    {
        $this->lastSeen = $lastSeen;
    }

    /**
     * @param $title
     * @param $status
     * @param $typePush
     * @param $targeting
     */
    public function update($title, $status, $typePush, $targeting){
        $this->title = json_encode($title);
        $this->status = $status;
        $this->typePush = $typePush;
        $this->targeting = $targeting;
    }
}
