<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subscribers
 *
 * @ORM\Table(name="subscribers")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubscribersRepository")
 */
class Subscribers
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
    private $subscriber_id;

    /**
     * @ORM\Column(type="string")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $gender;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $locale;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $timezone;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastInteraction;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateSubscribed;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastSaveAvatar;

    /**
     * Subscribers constructor.
     * @param $page_id
     * @param $subscriber_id
     * @param $firstName
     * @param $lastName
     * @param $gender
     * @param $locale
     * @param $timezone
     * @param $avatar
     */
    public function __construct($page_id, $subscriber_id, $firstName, $lastName, $gender, $locale, $timezone, $avatar)
    {
        $this->page_id = $page_id;
        $this->subscriber_id = $subscriber_id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->gender = $gender;
        $this->locale = $locale;
        $this->timezone = $timezone;
        $this->avatar = $avatar;
        $this->lastInteraction = new \DateTime();
        $this->dateSubscribed = new \DateTime();
        $this->status = true;
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
    public function getSubscriberId()
    {
        return $this->subscriber_id;
    }

    /**
     * @param mixed $subscriber_id
     */
    public function setSubscriberId($subscriber_id)
    {
        $this->subscriber_id = $subscriber_id;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param mixed $timezone
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param mixed $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return mixed
     */
    public function getLastInteraction()
    {
        return $this->lastInteraction;
    }

    /**
     * @param mixed $lastInteraction
     */
    public function setLastInteraction($lastInteraction)
    {
        $this->lastInteraction = $lastInteraction;
    }

    /**
     * @return mixed
     */
    public function getDateSubscribed()
    {
        return $this->dateSubscribed;
    }

    /**
     * @param mixed $dateSubscribed
     */
    public function setDateSubscribed($dateSubscribed)
    {
        $this->dateSubscribed = $dateSubscribed;
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
    public function getLastSaveAvatar()
    {
        return $this->lastSaveAvatar;
    }

    /**
     * @param mixed $lastSaveAvatar
     */
    public function setLastSaveAvatar($lastSaveAvatar)
    {
        $this->lastSaveAvatar = $lastSaveAvatar;
    }
}
