<?php


namespace AppBundle\Helper;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\SequencesItems;
use AppBundle\Helper\Flow\FlowHelper;
use Doctrine\ORM\EntityManager;

class SequencesHelper
{
    /**
     * @param EntityManager $em
     * @param $items
     * @return array
     */
    public static function generateSequenceResponse(EntityManager $em, $items){
        $sequences = [];
        if(!empty($items)){
            foreach ($items as $sequence){
                if($sequence instanceof Sequences){
                    $sequences[] = self::generateSequenceOneResponse($em, $sequence);
                }
            }
        }
        return $sequences;
    }

    /**
     * @param EntityManager $em
     * @param Sequences $sequence
     * @return array
     */
    public static function generateSequenceOneResponse(EntityManager $em, Sequences $sequence){
        $countSubscribersSequence = $em->getRepository("AppBundle:SubscribersSequences")->count(['sequence'=>$sequence]);
        $sequenceItems = $em->getRepository("AppBundle:SequencesItems")->findBy(['sequence'=>$sequence]);
        $openRate = 0;
        $ctr = 0;
        if(!empty($sequenceItems)){
            $sumSent = 0;
            $sumOpened = 0;
            $sumClicked = 0;
            foreach ($sequenceItems as $sequenceItem){
                if($sequenceItem instanceof SequencesItems && $sequenceItem->getFlow() instanceof Flows){
                    $flowStartItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$sequenceItem->getFlow(), 'startStep'=>true]);
                    if($flowStartItem instanceof FlowItems){
                        $sumSent = $sumSent + $flowStartItem->getDelivered();
                        $sumOpened = $sumOpened + $flowStartItem->getOpened();
                        $sumClicked = $sumClicked + $flowStartItem->getClicked();
                    }
                }
            }
            if($sumSent > 0){
                if($sumOpened > 0){
                    $openRate = round((($sumOpened/$sumSent)*100), 2);

                    if($sumClicked > 0){
                        $ctr = round((($sumClicked/$sumOpened)*100), 2);
                    }
                }
            }
        }

        return [
            'id' => $sequence->getId(),
            'title' => $sequence->getTitle(),
            'countSubscribers' => $countSubscribersSequence,
            'countItems' => count($sequenceItems),
            'openRate' => $openRate,
            'ctr' => $ctr
        ];
    }

    /**
     * @param EntityManager $em
     * @param SequencesItems $sequencesItem
     * @return array
     */
    public static function generateSequenceItemResponse(EntityManager $em, SequencesItems $sequencesItem){
        $flowResponse = null;
        $flowStartItem = null;
        if($sequencesItem->getFlow() instanceof Flows){
            $flowResponse = FlowHelper::getFlowResponse($em, $sequencesItem->getFlow());
            $flowStartItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$sequencesItem->getFlow(), 'startStep'=>true]);
        }
        $result = [
            'id' => $sequencesItem->getId(),
            'flow' => $flowResponse,
            'delay' => $sequencesItem->getDelay(),
            'number' => $sequencesItem->getNumber(),
            'status' => $sequencesItem->getStatus(),
            'sent' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getSent() : 0,
            'delivered' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getDelivered() : 0,
            'opened' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getOpened() : 0,
            'clicked' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getClicked() : 0
        ];
        return $result;
    }
}
