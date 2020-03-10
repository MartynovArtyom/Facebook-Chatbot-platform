<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomRefParameter
 *
 * @ORM\Table(name="custom_ref_parameter")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomRefParameterRepository")
 */
class CustomRefParameter
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
     * @var Widget
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Widget", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $widget;

    /**
     * @ORM\Column(type="string")
     */
    private $parameter;

    /**
     * CustomRefParameter constructor.
     * @param $page_id
     * @param Widget $widget
     * @param $parameter
     */
    public function __construct($page_id, Widget $widget, $parameter)
    {
        $this->page_id = $page_id;
        $this->widget = $widget;
        $this->parameter = $parameter;
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

    /**
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param mixed $parameter
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
    }
}
