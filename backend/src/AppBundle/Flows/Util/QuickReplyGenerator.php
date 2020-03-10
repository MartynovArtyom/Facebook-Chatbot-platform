<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 17:31
 */

namespace AppBundle\Flows\Util;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;
use pimax\Messages\QuickReplyButton;

/**
 * Class QuickReplyGenerator
 * @package AppBundle\Flows\Util
 */
class QuickReplyGenerator implements QuickReplyGeneratorInterface
{
    /**
     * @var array
     */
    protected $quickReplyItems = [];

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param $subscriber
     * @return mixed
     */
    public function generateQuickReplyItems(EntityManager $em, Page $page, FlowItems $flowItem, $subscriber){
        if(!empty($flowItem->getQuickReply())){
            $textVarReplacement = new TextVarReplacement();
            foreach($flowItem->getQuickReply() as $quickReply){
                if(isset($quickReply['uuid']) && !empty($quickReply['uuid']) && isset($quickReply['title']) && !empty($quickReply['title'])){
                    $title = $textVarReplacement->replaceTextVar($em, $quickReply['title'], $page, $subscriber);
                    $this->quickReplyItems[] = new QuickReplyButton(QuickReplyButton::TYPE_TEXT,
                        $title,
                        //CHATBO_NEW  :TYPE           :FLOW_ID                        :FLOW_ITEM_UUID          :QUICK_UUID
                        'CHATBO_NEW:QUICK_REPLY:'.$flowItem->getFlow()->getId().':'.$flowItem->getUuid().':'.$quickReply['uuid']
                    );
                }
            }
        }

        return $this->quickReplyItems;
    }

    /**
     * @param FlowItems $flowItem
     * @return array|mixed
     */
    public function generateJSONQuickReplyItems(FlowItems $flowItem){
        if(!empty($flowItem->getQuickReply())){
            $textVarReplacement = new TextVarReplacement();
            foreach($flowItem->getQuickReply() as $quickReply){
                if(isset($quickReply['uuid']) && !empty($quickReply['uuid']) && isset($quickReply['title']) && !empty($quickReply['title'])){
                    if($textVarReplacement->checkTextVar($quickReply['title'])){
                        $this->quickReplyItems[] = new QuickReplyButton(QuickReplyButton::TYPE_TEXT,
                            $quickReply['title'],
                            //CHATBO_NEW  :TYPE           :FLOW_ID                        :FLOW_ITEM_UUID          :QUICK_UUID
                            'CHATBO_NEW:QUICK_REPLY:'.$flowItem->getFlow()->getId().':'.$flowItem->getUuid().':'.$quickReply['uuid']
                        );
                    }
                    else{
                        return [
                            'message' => "You cannot use Variables in JSON Growth Tool Opt-In message",
                            'result' => false
                        ];

                    }
                }
            }
        }

        return [
            'items' => $this->quickReplyItems,
            'result' => true
        ];
    }
}