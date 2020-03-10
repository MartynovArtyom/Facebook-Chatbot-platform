<?php


namespace AppBundle\Campaigns;


use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Pages\ClonePage;
use Doctrine\ORM\EntityManager;

/**
 * Class SequencesShare
 * @package AppBundle\Campaigns
 */
class SequencesShare extends ClonePage
{
    /**
     * @var Sequences
     */
    protected $sequence;

    /**
     * CampaignsShare constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param Sequences $sequence
     */
    public function __construct(EntityManager $em, Page $page, Sequences $sequence)
    {
        $this->em = $em;
        $this->clonePage = $page;
        $this->sequence = $sequence;
        $this->folder = null;
    }

    /**
     * @return Sequences|object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function share(){
        $newSequenceID = $this->replaceSequence($this->sequence);
        $newSequence = $this->em->getRepository("AppBundle:Sequences")->find($newSequenceID);

        return $newSequence;
    }
}
