<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CustomFields
 *
 * @ORM\Table(name="custom_fields")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomFieldsRepository")
 */
class CustomFields
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
     *     groups={"customFields"}
     * )
     * @ORM\Column(type="string")
     */
    private $page_id;

    /**
     * @Assert\NotBlank(
     *     message="name should not be empty",
     *     groups={"customFields"}
     * )
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @Assert\NotBlank(
     *     message="type should not be empty",
     *     groups={"customFields"}
     * )
     * @Assert\GreaterThan(
     *     message="type Invalid value",
     *     value="0",
     *     groups={"customFields"}
     * )
     * @Assert\LessThan(
     *     message="type Invalid value",
     *     value="6",
     *     groups={"customFields"}
     * )
     * @ORM\Column(type="integer")
     *
     * 1 = Text
     * 2 = Number
     * 3 = Date
     * 4 = DateTime
     * 5 = Boolean
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * CustomFields constructor.
     * @param $page_id
     * @param $name
     * @param $type
     * @param null $description
     * @param bool $status
     */
    public function __construct($page_id, $name, $type, $description=NULL, $status=true)
    {
        $this->page_id = $page_id;
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->status = $status;
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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
