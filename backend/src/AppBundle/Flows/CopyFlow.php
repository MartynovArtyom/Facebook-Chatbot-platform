<?php


namespace AppBundle\Flows;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Folders;
use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;

/**
 * Class CopyFlow
 * @package AppBundle\Flows
 */
class CopyFlow implements CopyFlowInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var Flows
     */
    protected $flow;

    /**
     * CopyFlow constructor.
     * @param EntityManager $em
     * @param Page $page
     * @param Flows $flow
     */
    public function __construct(EntityManager $em, Page $page, Flows $flow)
    {
        $this->em = $em;
        $this->page = $page;
        $this->flow = $flow;
    }

    /**
     * @param $flowType
     * @return Flows
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function copy($flowType = Flows::FLOW_TYPE_CONTENT){
        //Copy Flow
        $newFlow = new Flows($this->page->getPageId(), $this->flow->getName().' copy', $flowType, $this->flow->getFolder(), true);
        $this->em->persist($newFlow);
        $this->em->flush();

        //Copy FLow Items
        $this->copyFlowItems($newFlow);

        return $newFlow;
    }

    /**
     * @param $newFlow
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function copyFlowItems($newFlow){
        $flowItems = $this->em->getRepository("AppBundle:FlowItems")->findBy(['flow'=>$this->flow]);
        if(!empty($flowItems)){
            foreach ($flowItems as $flowItem){
                if($flowItem instanceof FlowItems){
                    $newFlowItem = new FlowItems(
                        $newFlow,
                        $flowItem->getUuid(),
                        $flowItem->getName(),
                        $flowItem->getType(),
                        $flowItem->getItems(),
                        $flowItem->getQuickReply(),
                        $flowItem->getStartStep(),
                        $flowItem->getNextStep(),
                        $flowItem->getPositionX(),
                        $flowItem->getPositionY(),
                        $flowItem->getArrow(),
                        $flowItem->getHideNextStep()
                    );
                    $this->em->persist($newFlowItem);
                    $this->em->flush();
                }
            }
        }
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @param EntityManager $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return Flows
     */
    public function getFlow()
    {
        return $this->flow;
    }

    /**
     * @param Flows $flow
     */
    public function setFlow($flow)
    {
        $this->flow = $flow;
    }
}
