<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRef
 *
 * @ORM\Table(name="user_ref")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRefRepository")
 */
class UserRef
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
    private $user_ref;

    /**
     * @var Widget
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Widget", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     */
    private $widget;

    /**
     * UserRef constructor.
     * @param $user_ref
     * @param Widget $widget
     */
    public function __construct($user_ref, Widget $widget)
    {
        $this->user_ref = $user_ref;
        $this->widget = $widget;
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
    public function getUserRef()
    {
        return $this->user_ref;
    }

    /**
     * @param mixed $user_ref
     */
    public function setUserRef($user_ref)
    {
        $this->user_ref = $user_ref;
    }

    /**
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param Widget $widget
     */
    public function setWidget($widget)
    {
        $this->widget = $widget;
    }
}
