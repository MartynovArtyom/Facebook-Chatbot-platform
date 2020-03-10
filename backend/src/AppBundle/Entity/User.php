<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping\AttributeOverride;
use Doctrine\ORM\Mapping\Column;


/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\AttributeOverrides({
 *      @AttributeOverride(name="password",
 *          column=@Column(
 *              nullable = true
 *          )
 *      )
 * })
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="facebook_id",type="string")
     */
    protected $facebook_id;

    /**
     * @ORM\Column(name="facebook_access_token", type="string", length=255)
     */
    protected $facebook_access_token;

    /**
     * @Column(type="string")
     */
    protected $firstName;

    /**
     * @Column(type="string")
     */
    protected $lastName;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $avatar;

    /**
     * @Column(type="boolean", options={"default":false})
     */
    protected $first_popup;

    /**
     * @Column(type="integer", options={"default":0}, nullable=true)
     */
    protected $limitSubscribers;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $orderId;

    /**
     * @var DigistoreProduct
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\DigistoreProduct", fetch="EAGER")
     * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    protected $product;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $quentnId;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $trialEnd;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->first_popup = false;
        $this->limitSubscribers = 0;
        $this->lastLogin = new \DateTime();
        $this->created = new \DateTime();
        $this->trialEnd = new \DateTime('+14 days');
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
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * @param mixed $facebook_id
     */
    public function setFacebookId($facebook_id)
    {
        $this->facebook_id = $facebook_id;
    }

    /**
     * @return mixed
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * @param mixed $facebook_access_token
     */
    public function setFacebookAccessToken($facebook_access_token)
    {
        $this->facebook_access_token = $facebook_access_token;
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
    public function getFirstPopup()
    {
        return $this->first_popup;
    }

    /**
     * @param mixed $first_popup
     */
    public function setFirstPopup($first_popup)
    {
        $this->first_popup = $first_popup;
    }

    /**
     * @return mixed
     */
    public function getLimitSubscribers()
    {
        return $this->limitSubscribers;
    }

    /**
     * @param mixed $limitSubscribers
     */
    public function setLimitSubscribers($limitSubscribers)
    {
        $this->limitSubscribers = $limitSubscribers;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param mixed $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return DigistoreProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param DigistoreProduct $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return mixed
     */
    public function getQuentnId()
    {
        return $this->quentnId;
    }

    /**
     * @param mixed $quentnId
     */
    public function setQuentnId($quentnId)
    {
        $this->quentnId = $quentnId;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getTrialEnd()
    {
        return $this->trialEnd;
    }

    /**
     * @param mixed $trialEnd
     */
    public function setTrialEnd($trialEnd)
    {
        $this->trialEnd = $trialEnd;
    }
}

