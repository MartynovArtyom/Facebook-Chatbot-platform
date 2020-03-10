<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubscribersCustomFields
 *
 * @ORM\Table(name="subscribers_custom_fields")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SubscribersCustomFieldsRepository")
 */
class SubscribersCustomFields
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
     * @var Subscribers
     *
     * @ORM\ManyToOne(targetEntity="Subscribers", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $subscriber;

    /**
     * @var CustomFields
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CustomFields", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $customField;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    private $value;

    /**
     * SubscribersCustomFields constructor.
     * @param Subscribers $subscriber
     * @param CustomFields $customField
     * @param $value
     */
    public function __construct(Subscribers $subscriber, CustomFields $customField, $value)
    {
        $this->subscriber = $subscriber;
        $this->customField = $customField;
        $this->value = $value;
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
     * @return Subscribers
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * @param Subscribers $subscriber
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @return CustomFields
     */
    public function getCustomField()
    {
        return $this->customField;
    }

    /**
     * @param CustomFields $customField
     */
    public function setCustomField($customField)
    {
        $this->customField = $customField;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
