<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NotificationRepository")
 */
class Notification
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
     *     groups={"notification"}
     * )
     * @ORM\Column(type="string")
     */
    private $page_id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @Assert\NotBlank(
     *     message="Email should be not empty",
     *     groups={"notification"}
     * )
     * @Assert\Email(
     *     message = "Email is invalid.",
     *     groups={"notification"}
     * )
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @Assert\NotBlank(
     *     message="Type should be not empty",
     *     groups={"notification"}
     * )
     * @Assert\Range(
     *     min = 1,
     *     max = 3,
     *     minMessage = "Type is invalid",
     *     maxMessage = "Type is invalid",
     *     groups={"notification"}
     * )
     * 1 = Daily
     * 2 = Weekly
     * 3 = Monthly
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @Assert\NotNull(
     *     message="Status should be not empty",
     *     groups={"notification"}
     * )
     *  @Assert\Type(
     *     type="bool",
     *     message="Status should be boolean type",
     *     groups={"notification"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * Notification constructor.
     * @param $page_id
     * @param User $user
     * @param $email
     */
    public function __construct($page_id, User $user, $email)
    {
        $this->page_id = $page_id;
        $this->user = $user;
        $this->email = $email;
        $this->type = 1;
        $this->status = false;
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
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
