<?php


namespace AppBundle\Flows;


use AppBundle\Entity\FlowItems;
use Doctrine\ORM\EntityManager;

/**
 * Class SelectFlowItem
 * @package AppBundle\Flows
 */
class SelectFlowItem implements SelectFlowItemInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var FlowItems
     */
    protected $flowItem;

    /**
     * @var array
     */
    protected $items;

    /**
     * SelectFlowItem constructor.
     * @param EntityManager $em
     * @param FlowItems $flowItem
     */
    public function __construct(EntityManager $em, FlowItems $flowItem)
    {
        $this->em = $em;
        $this->flowItem = $flowItem;
        $this->items = [];
    }

    /**
     * @return array
     */
    public function selectItems(){
        $this->findItems($this->flowItem);

        return [
            'item' => $this->flowItem->getResponseNullableStats(),
            'items' => $this->items
        ];
    }

    /**
     * @param FlowItems $flowItem
     */
    private function findItems(FlowItems $flowItem){
        //Find in next step
        $includeItemsUuid = $this->findNextStep($flowItem, []);
        //Find in qr
        $includeItemsUuid = $this->findQuickReply($flowItem, $includeItemsUuid);
        //Find in message items
        $includeItemsUuid = $this->findMessageItems($flowItem, $includeItemsUuid);
        //Get Include Items
        $this->findIncludeItems($includeItemsUuid);
    }

    /**
     * @param FlowItems $flowItem
     * @param $includeItemsUuid
     * @return array
     */
    private function findNextStep(FlowItems $flowItem, $includeItemsUuid){
        if(!empty($flowItem->getNextStep()) && !in_array($flowItem->getNextStep(), $includeItemsUuid)){
            $includeItemsUuid[] = $flowItem->getNextStep();
        }

        return $includeItemsUuid;
    }

    /**
     * @param FlowItems $flowItem
     * @param $includeItemsUuid
     * @return array
     */
    private function findQuickReply(FlowItems $flowItem, $includeItemsUuid){
        if(!empty($flowItem->getQuickReply())){
            foreach ($flowItem->getQuickReply() as $quickReply){
                if(array_key_exists('next_step', $quickReply) && !empty($quickReply['next_step']) && !in_array($quickReply['next_step'], $includeItemsUuid)){
                    $includeItemsUuid[] = $quickReply['next_step'];
                }
            }
        }

        return $includeItemsUuid;
    }

    /**
     * @param FlowItems $flowItem
     * @param $includeItemsUuid
     * @return array|mixed
     */
    private function findMessageItems(FlowItems $flowItem, $includeItemsUuid){
        if(!empty($flowItem->getItems())){
            switch ($flowItem->getType()){
                case FlowItems::TYPE_SEND_MESSAGE:
                    $includeItemsUuid = $this->findMessageItemsSendMessage($flowItem->getItems(), $includeItemsUuid);
                    break;
                case FlowItems::TYPE_CONDITION:
                    $includeItemsUuid = $this->findMessageItemsCondition($flowItem->getItems(), $includeItemsUuid);
                    break;

                case FlowItems::TYPE_RANDOMIZER:
                    $includeItemsUuid = $this->findMessageItemsRandomizer($flowItem->getItems(), $includeItemsUuid);
                    break;
            }
        }

        return $includeItemsUuid;
    }

    /**
     * @param $includeItemsUuid
     */
    private function findIncludeItems($includeItemsUuid){
        if(!empty($includeItemsUuid)){
            foreach ($includeItemsUuid as $uuid){
                if(!array_key_exists($uuid, $this->items)){
                    $includeFlowItem = $this->em->getRepository("AppBundle:FlowItems")->findOneBy(['uuid'=>$uuid, 'flow'=>$this->flowItem->getFlow()]);
                    if($includeFlowItem instanceof FlowItems){
                        $this->items[$uuid] = $includeFlowItem->getResponseNullableStats();
                        $this->findItems($includeFlowItem);
                    }
                }
            }
        }
    }

    /**
     * @param $items
     * @param $includeItemsUuid
     * @return array
     */
    private function findMessageItemsSendMessage($items, $includeItemsUuid){
        foreach ($items as $item){
            if (isset($item['type']) && !empty($item['type'])) {
                switch ($item['type']){
                    //TYPE TEXT
                    case "text":
                    case "image":
                    case "video":
                        if(array_key_exists('params', $item) && array_key_exists('buttons', $item['params']) && !empty($item['params']['buttons'])){
                            foreach ($item['params']['buttons'] as $button){
                                if(array_key_exists('next_step', $button) && !empty($button['next_step']) && !in_array($button['next_step'], $includeItemsUuid)){
                                    $includeItemsUuid[] = $button['next_step'];
                                }
                            }
                        }
                        break;

                    //TYPE CARD|GALLERY
                    case "card":
                    case "gallery":
                        if(array_key_exists('params', $item) && array_key_exists('cards_array', $item['params']) && !empty($item['params']['cards_array'])){
                            foreach ($item['params']['cards_array'] as $card){
                                if(array_key_exists('buttons', $card) && !empty($card['buttons'])){
                                    foreach ($card['buttons'] as $button){
                                        if(array_key_exists('next_step', $button) && !empty($button['next_step']) && !in_array($button['next_step'], $includeItemsUuid)){
                                            $includeItemsUuid[] = $button['next_step'];
                                        }
                                    }
                                }
                            }
                        }
                        break;
                    //TYPE LIST
                    case "list":
                        if(array_key_exists('params', $item) && array_key_exists('list_array', $item['params']) && !empty($item['params']['list_array'])) {
                            foreach ($item['params']['list_array'] as $list) {
                                if(array_key_exists('buttons', $list) && !empty($list['buttons'])){
                                    foreach ($list['buttons'] as $button){
                                        if(array_key_exists('next_step', $button) && !empty($button['next_step']) && !in_array($button['next_step'], $includeItemsUuid)){
                                            $includeItemsUuid[] = $button['next_step'];
                                        }
                                    }
                                }
                            }
                        }
                        if(array_key_exists('params', $item) && array_key_exists('buttons', $item['params']) && !empty($item['params']['buttons'])){
                            foreach ($item['params']['buttons'] as $button){
                                if(array_key_exists('next_step', $button) && !empty($button['next_step']) && !in_array($button['next_step'], $includeItemsUuid)){
                                    $includeItemsUuid[] = $button['next_step'];
                                }
                            }
                        }
                        break;

                    case "user_input":
                        if(array_key_exists('params', $item) && array_key_exists('buttons', $item['params']) && !empty($item['params']['buttons'])){
                            foreach ($item['params']['buttons'] as $button){
                                if(array_key_exists('next_step', $button) && !empty($button['next_step']) && !in_array($button['next_step'], $includeItemsUuid)){
                                    $includeItemsUuid[] = $button['next_step'];
                                }
                            }
                        }
                        break;
                }
            }
        }

        return $includeItemsUuid;
    }

    /**
     * @param $items
     * @param $includeItemsUuid
     * @return mixed
     */
    private function findMessageItemsCondition($items, $includeItemsUuid){
        foreach ($items as $item){
            if(
                array_key_exists('invalid_step', $item)
                && array_key_exists('next_step', $item['invalid_step'])
                && !empty($item['invalid_step']['next_step'])
                && !in_array($item['invalid_step']['next_step'], $includeItemsUuid)
            ){
                $includeItemsUuid[] = $item['invalid_step']['next_step'];
            }
            if(
                array_key_exists('valid_step', $item)
                && array_key_exists('next_step', $item['valid_step'])
                && !empty($item['valid_step']['next_step'])
                && !in_array($item['valid_step']['next_step'], $includeItemsUuid)
            ){
                $includeItemsUuid[] = $item['valid_step']['next_step'];
            }
        }

        return $includeItemsUuid;
    }

    /**
     * @param $items
     * @param $includeItemsUuid
     * @return array
     */
    private function findMessageItemsRandomizer($items, $includeItemsUuid){
        foreach ($items as $item){
            if(array_key_exists('randomData', $item) && !empty($item['randomData'])){
                foreach ($item['randomData'] as $random){
                    if(array_key_exists('next_step', $random) && !empty($random['next_step']) && !in_array($random['next_step'], $includeItemsUuid)){
                        $includeItemsUuid[] = $random['next_step'];
                    }
                }
            }
        }

        return $includeItemsUuid;
    }
}
