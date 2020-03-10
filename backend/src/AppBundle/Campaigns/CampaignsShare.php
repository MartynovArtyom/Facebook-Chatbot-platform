<?php


namespace AppBundle\Campaigns;



use AppBundle\Entity\Page;
use AppBundle\Entity\Widget;
use AppBundle\Pages\ClonePage;
use Doctrine\ORM\EntityManager;

/**
 * Class CampaignsShare
 * @package AppBundle\Campaigns
 */
class CampaignsShare extends ClonePage
{
    /**
     * @var Widget
     */
    protected $campaign;

    /**
     * CampaignsShare constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param Widget $campaign
     */
    public function __construct(EntityManager $em, Page $page, Widget $campaign)
    {
        $this->em = $em;
        $this->clonePage = $page;
        $this->campaign = $campaign;
        $this->folder = null;
    }

    /**
     * @return Widget|object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function share(){
        $newCampaignID = $this->replaceWidget($this->campaign);
        $newCampaign = $this->em->getRepository("AppBundle:Widget")->find($newCampaignID);

        return $newCampaign;
    }
}
