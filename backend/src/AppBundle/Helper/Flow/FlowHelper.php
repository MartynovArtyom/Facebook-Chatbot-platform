<?php
/**
 * Created by PhpStorm.
 * Date: 02.11.18
 * Time: 15:13
 */

namespace AppBundle\Helper\Flow;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\FlowItemsDraft;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Folders;
use Doctrine\ORM\EntityManager;

class FlowHelper
{
    /**
     * @param EntityManager $em
     * @param Flows $flow
     * @return array
     */
    public static function getFlowDataResponse(EntityManager $em, Flows $flow){
        //DRAFT ITEMS
        $draft = false;
        $draftItems = [];
        $draftFlow = $em->getRepository("AppBundle:FlowItemsDraft")->findOneBy(['flow'=>$flow]);
        if($draftFlow instanceof FlowItemsDraft){
            $draft = true;
            $draftItems = $draftFlow->getItems();
        }

        // PUBLISH ITEMS
        $items = [];
        $flowItems = $em->getRepository("AppBundle:FlowItems")->findBy(['flow'=>$flow]);
        if(!empty($flowItems)){
            foreach ($flowItems as $flowItem){
                if($flowItem instanceof FlowItems){
                    $flowItemResponse = $flowItem->getResponse();
                    $flowItemResponse['countResponses'] = $em->getRepository("AppBundle:UserInputResponse")->count(['flowItem'=>$flowItem]);
                    $items[] = $flowItemResponse;
                }
            }
        }


        return [
            'id' => $flow->getId(),
            'name' => $flow->getName(),
            'draft' => $draft,
            'status' => $flow->getStatus(),
            'draftItems' => $draftItems,
            'items' => $items
        ];
    }

    /**
     * @param EntityManager $em
     * @param Flows $flow
     * @return array
     */
    public static function getFlowResponse(EntityManager $em, Flows $flow){
        $draft = false;
        $draftFlow = $em->getRepository("AppBundle:FlowItemsDraft")->findOneBy(['flow'=>$flow]);
        if($draftFlow instanceof FlowItemsDraft){
            $draft = true;
        }

        return [
            'id' => $flow->getId(),
            'name' => $flow->getName(),
            'type' => $flow->getType(),
            'folderID' => ($flow->getFolder() instanceof Folders) ? $flow->getFolder()->getId() : null,
            'modified' => $flow->getModified(),
            'status' => $flow->getStatus(),
            'draft' => $draft
        ];
    }

    /**
     * @param EntityManager $em
     * @param array $flows
     * @return array
     */
    public static function getFlowsArrayResponse(EntityManager $em, array $flows){
        $result = [];
        if(!empty($flows)){
            foreach ($flows as $flow){
                if($flow instanceof Flows){
                    $result[] = self::getFlowResponse($em, $flow);
                }
            }
        }

        return $result;
    }
}
