<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InviteLinkAdmin
 *
 * @ORM\Table(name="invite_link_admin")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InviteLinkAdminRepository")
 */
class InviteLinkAdmin
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
    private $token;

    /**
     * @ORM\Column(type="string")
     */
    private $timeExpired;

    /**
     * 1=Admin, 2=Editor, 3=Live Chat Agent, 4=Viewer
     * @ORM\Column(type="integer")
     */
    private $role;

    /**
     * InviteLinkAdmin constructor.
     * @param $page_id
     * @param $token
     * @param $timeExpired
     * @param $role
     */
    public function __construct($page_id, $token, $timeExpired, $role)
    {
        $this->page_id = $page_id;
        $this->token = $token;
        $this->timeExpired = $timeExpired;
        $this->role = $role;
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
    public function getTimeExpired()
    {
        return $this->timeExpired;
    }

    /**
     * @param mixed $timeExpired
     */
    public function setTimeExpired($timeExpired)
    {
        $this->timeExpired = $timeExpired;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }
}
