<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZapierWebhook
 *
 * @ORM\Table(name="zapier_webhook")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ZapierWebhookRepository")
 */
class ZapierWebhook
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
    private $target_url;

    /**
     * @ORM\Column(type="string")
     */
    private $event;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $field_id;

    /**
     * ZapierWebhook constructor.
     * @param $page_id
     * @param $target_url
     * @param $event
     * @param null $field_id
     */
    public function __construct($page_id, $target_url, $event, $field_id = null)
    {
        $this->page_id = $page_id;
        $this->target_url = $target_url;
        $this->event = $event;
        $this->field_id = $field_id;
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
    public function getTargetUrl()
    {
        return $this->target_url;
    }

    /**
     * @param mixed $target_url
     */
    public function setTargetUrl($target_url)
    {
        $this->target_url = $target_url;
    }

    /**
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param mixed $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return mixed
     */
    public function getFieldId()
    {
        return $this->field_id;
    }

    /**
     * @param mixed $field_id
     */
    public function setFieldId($field_id)
    {
        $this->field_id = $field_id;
    }
}
