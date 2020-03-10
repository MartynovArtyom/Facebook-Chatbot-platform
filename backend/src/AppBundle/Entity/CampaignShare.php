<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CampaignShare
 *
 * @ORM\Table(name="campaign_share")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CampaignShareRepository")
 */
class CampaignShare
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
     * @ORM\Column(type="integer")
     */
    private $campaignID;

    /**
     * @ORM\Column(type="string")
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * CampaignShare constructor.
     * @param $campaignID
     * @throws \Exception
     */
    public function __construct($campaignID)
    {
        $this->campaignID = $campaignID;
        $this->status = false;
        $this->token = bin2hex(random_bytes(16));;
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
    public function getCampaignID()
    {
        return $this->campaignID;
    }

    /**
     * @param mixed $campaignID
     */
    public function setCampaignID($campaignID)
    {
        $this->campaignID = $campaignID;
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
}
