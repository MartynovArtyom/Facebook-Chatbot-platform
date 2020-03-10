<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SequenceShare
 *
 * @ORM\Table(name="sequence_share")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SequenceShareRepository")
 */
class SequenceShare
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
     * @var Sequences
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Sequences", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $sequence;

    /**
     * @ORM\Column(type="string")
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * SequenceShare constructor.
     * @param Sequences $sequence
     * @throws \Exception
     */
    public function __construct(Sequences $sequence)
    {
        $this->sequence = $sequence;
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
     * @return Sequences
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param Sequences $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
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
