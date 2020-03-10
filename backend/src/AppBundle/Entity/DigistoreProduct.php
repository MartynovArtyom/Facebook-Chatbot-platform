<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * DigistoreProduct
 *
 * @ORM\Table(name="digistore_product")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DigistoreProductRepository")
 * @UniqueEntity(
 *     "productId",
 *     message="productId already use",
 *     groups={"product"}
 * )
 */
class DigistoreProduct
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
     *     message="productId is required",
     *     groups={"product"}
     * )
     * @ORM\Column(type="integer", unique=true)
     */
    private $productId;

    /**
     * @Assert\NotBlank(
     *     message="name is required",
     *     groups={"product"}
     * )
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @Assert\NotBlank(
     *     message="label is required",
     *     groups={"product"}
     * )
     * @ORM\Column(type="string")
     */
    private $label;

    /**
     * @Assert\GreaterThan(
     *     value=0,
     *     message="limitSubscribers should be greater 0",
     *     groups={"product"}
     * )
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limitSubscribers;

    /**
     * @Assert\GreaterThan(
     *     value=0,
     *     message="limitCompany should be greater 0",
     *     groups={"product"}
     * )
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limitCompany;

    /**
     * @Assert\GreaterThan(
     *     value=0,
     *     message="limitSequences should be greater 0",
     *     groups={"product"}
     * )
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limitSequences;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="comments should be boolean type",
     *     groups={"product"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $comments;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="downloadPsid should be boolean type",
     *     groups={"product"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $downloadPsid;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="zapier should be boolean type",
     *     groups={"product"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $zapier;

    /**
     * @Assert\Choice(
     *     {false,true},
     *     strict=true,
     *     message="admins should be boolean type",
     *     groups={"product"}
     * )
     * @ORM\Column(type="boolean")
     */
    private $admins;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $quentnUrl;

    /**
     * @Assert\GreaterThan(
     *     value=0,
     *     message="limitedQuentn should be greater 0",
     *     groups={"product"}
     * )
     * @ORM\Column(type="integer", nullable=true)
     */
    private $limitedQuentn;

    /**
     * DigistoreProduct constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        foreach($params as $key => $value) {
            if (property_exists($this, $key) && $key != 'id') {
                $this->$key = $value;
            }
        }
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
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param mixed $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
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
    public function getLimitCompany()
    {
        return $this->limitCompany;
    }

    /**
     * @param mixed $limitCompany
     */
    public function setLimitCompany($limitCompany)
    {
        $this->limitCompany = $limitCompany;
    }

    /**
     * @return mixed
     */
    public function getLimitSequences()
    {
        return $this->limitSequences;
    }

    /**
     * @param mixed $limitSequences
     */
    public function setLimitSequences($limitSequences)
    {
        $this->limitSequences = $limitSequences;
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param mixed $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return mixed
     */
    public function getDownloadPsid()
    {
        return $this->downloadPsid;
    }

    /**
     * @param mixed $downloadPsid
     */
    public function setDownloadPsid($downloadPsid)
    {
        $this->downloadPsid = $downloadPsid;
    }

    /**
     * @return mixed
     */
    public function getZapier()
    {
        return $this->zapier;
    }

    /**
     * @param mixed $zapier
     */
    public function setZapier($zapier)
    {
        $this->zapier = $zapier;
    }

    /**
     * @return mixed
     */
    public function getAdmins()
    {
        return $this->admins;
    }

    /**
     * @param mixed $admins
     */
    public function setAdmins($admins)
    {
        $this->admins = $admins;
    }

    /**
     * @return mixed
     */
    public function getQuentnUrl()
    {
        return $this->quentnUrl;
    }

    /**
     * @param mixed $quentnUrl
     */
    public function setQuentnUrl($quentnUrl)
    {
        $this->quentnUrl = $quentnUrl;
    }

    /**
     * @return mixed
     */
    public function getLimitedQuentn()
    {
        return $this->limitedQuentn;
    }

    /**
     * @param mixed $limitedQuentn
     */
    public function setLimitedQuentn($limitedQuentn)
    {
        $this->limitedQuentn = $limitedQuentn;
    }

    /**
     * @param array $params
     */
    public function update(array $params){
        foreach($params as $key => $value) {
            if (property_exists($this, $key) && $key != 'id') {
                $this->$key = $value;
            }
        }
    }

    /**
     * @return array
     */
    public function toArray(){
        return [
            'id' => $this->id,
            'productId' => $this->productId,
            'name' => $this->name,
            'label' => $this->label,
            'limitSubscribers' => $this->limitSubscribers,
            'limitCompany' => $this->limitCompany,
            'limitSequences' => $this->limitSequences,
            'comments' => $this->comments,
            'downloadPsid' => $this->downloadPsid,
            'zapier' => $this->zapier,
            'admins' => $this->admins,
            'quentnUrl' => $this->quentnUrl,
            'limitedQuentn' => $this->limitedQuentn,
        ];
    }
}
