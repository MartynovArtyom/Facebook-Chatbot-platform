<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 17:23
 */

namespace AppBundle\Flows\Util;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use Doctrine\ORM\EntityManager;
use pimax\Messages\MessageButton;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ButtonGenerator
 * @package AppBundle\Flows\Util
 */
class ButtonGenerator implements ButtonGeneratorInterface
{
    /**
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param $item
     * @param $subscriber
     * @param $itemID
     * @return array|mixed
     */
    public function generateButtonItems(EntityManager $em, Page $page, FlowItems $flowItem, $item, $subscriber, $itemID){
        $buttonsItems = [];
        $buttons = [];
        if(isset($item['params']) && isset($item['params']['buttons'])){
            $buttons = $item['params']['buttons'];
        }
        elseif (isset($item['buttons'])){
            $buttons = $item['buttons'];
        }

        if(!empty($buttons)){
            //GENERATE BUTTON ITEMS
            $textVarReplacement = new TextVarReplacement();
            $request = Request::createFromGlobals();
            foreach ($buttons as $button){
                if(array_key_exists('type', $button) && array_key_exists('uuid', $button) && !empty($button['uuid']) && array_key_exists('title', $button) && !empty($button['title'])){
                    $buttonTitle = $textVarReplacement->replaceTextVar($em, $button['title'], $page, $subscriber);
                    if($button['type'] == "open_website"){
                        if(array_key_exists('btnValue', $button)){
                            if($subscriber instanceof Subscribers){
                                $urlButton = $request->getSchemeAndHttpHost()."/v2/fetch/insights/button?flowID=".$flowItem->getFlow()->getId()."&flowItemID=".$flowItem->getUuid()."&itemID=".$itemID."&buttonID=".$button['uuid']."&url=".urlencode($button['btnValue'])."&subscriberID=".$subscriber->getId();
                            }
                            else{
                                $urlButton = $request->getSchemeAndHttpHost()."/v2/fetch/insights/button?flowID=".$flowItem->getFlow()->getId()."&flowItemID=".$flowItem->getUuid()."&itemID=".$itemID."&buttonID=".$button['uuid']."&url=".urlencode($button['btnValue']);
                            }
                            if(isset($button['viewSize']) && in_array($button['viewSize'], ['full', 'medium', 'compact'])){
                                if($button['viewSize'] == 'full'){
                                    $buttonsItems[] = new MessageButton(
                                        MessageButton::TYPE_WEB,
                                        $buttonTitle,
                                        $urlButton,
                                        'full',
                                        true
                                    );
                                }
                                elseif ($button['viewSize'] == 'medium'){
                                    $buttonsItems[] = new MessageButton(
                                        MessageButton::TYPE_WEB,
                                        $buttonTitle,
                                        $urlButton,
                                        'tall',
                                        true
                                    );
                                }
                                else{
                                    $buttonsItems[] = new MessageButton(
                                        MessageButton::TYPE_WEB,
                                        $buttonTitle,
                                        $urlButton,
                                        'compact',
                                        true
                                    );
                                }
                            }
                            else{
                                $buttonsItems[] = new MessageButton(
                                    MessageButton::TYPE_WEB,
                                    $buttonTitle,
                                    $urlButton
                                );
                            }
                        }
                    }
                    elseif ($button['type'] == "call_number"){
                        if(array_key_exists('btnValue', $button)) {
                            if(substr($button['btnValue'], 0, 1) == '+'){
                                $phone_number = $button['btnValue'];
                            }
                            else{
                                $phone_number = '+'.$button['btnValue'];
                            }
                            $buttonsItems[] = new MessageButton(
                                MessageButton::TYPE_CALL,
                                $buttonTitle,
                                $phone_number
                            );
                        }
                    }
                    else{
                        $buttonsItems[] = new MessageButton(
                            MessageButton::TYPE_POSTBACK,
                            $buttonTitle,
                            //CHATBO_NEW :TYPE        :FLOW_ID                      :FLOW_ITEM_UUID       :ITEM_UUID     :BUTTON_UUID
                            'CHATBO_NEW:BUTTON:'.$flowItem->getFlow()->getId().':'.$flowItem->getUuid().':'.$itemID.':'.$button['uuid']
                        );
                    }
                }
            }
        }


        return $buttonsItems;
    }

    /**
     * @param FlowItems $flowItem
     * @param $item
     * @param $itemID
     * @return array
     */
    public function generateJSONButtonItems(FlowItems $flowItem, $item, $itemID){
        $buttonsItems = [];
        $checkButton = false;
        $buttons = [];
        if(isset($item['params']) && isset($item['params']['buttons'])){
            $buttons = $item['params']['buttons'];
        }
        elseif (isset($item['buttons'])){
            $buttons = $item['buttons'];
        }

        if(!empty($buttons)){
            //GENERATE BUTTON ITEMS
            $textVarReplacement = new TextVarReplacement();
            foreach ($buttons as $button){
                if(array_key_exists('type', $button) && array_key_exists('uuid', $button) && !empty($button['uuid']) && array_key_exists('title', $button) && !empty($button['title'])){
                    if($textVarReplacement->checkTextVar($button['title'])){
                        if($button['type'] == "open_website"){
                            if(array_key_exists('btnValue', $button)){
                                $urlButton = "https://api.chatbo.de/v2/fetch/insights/button?flowID=".$flowItem->getFlow()->getId()."&flowItemID=".$flowItem->getUuid()."&itemID=".$itemID."&buttonID=".$button['uuid']."&url=".urlencode($button['btnValue']);
                                if(isset($button['viewSize']) && in_array($button['viewSize'], ['full', 'medium', 'compact'])){
                                    if($button['viewSize'] == 'full'){
                                        $buttonsItems[] = new MessageButton(
                                            MessageButton::TYPE_WEB,
                                            $button['title'],
                                            $urlButton,
                                            'full',
                                            true
                                        );
                                    }
                                    elseif ($button['viewSize'] == 'medium'){
                                        $buttonsItems[] = new MessageButton(
                                            MessageButton::TYPE_WEB,
                                            $button['title'],
                                            $urlButton,
                                            'tall',
                                            true
                                        );
                                    }
                                    else{
                                        $buttonsItems[] = new MessageButton(
                                            MessageButton::TYPE_WEB,
                                            $button['title'],
                                            $urlButton,
                                            'compact',
                                            true
                                        );
                                    }
                                }
                                else{
                                    $buttonsItems[] = new MessageButton(
                                        MessageButton::TYPE_WEB,
                                        $button['title'],
                                        $urlButton
                                    );
                                }
                            }
                        }
                        elseif ($button['type'] == "call_number"){
                            if(array_key_exists('btnValue', $button)) {
                                if(substr($button['btnValue'], 0, 1) == '+'){
                                    $phone_number = $button['btnValue'];
                                }
                                else{
                                    $phone_number = '+'.$button['btnValue'];
                                }
                                $buttonsItems[] = new MessageButton(
                                    MessageButton::TYPE_CALL,
                                    $button['title'],
                                    $phone_number
                                );
                            }
                        }
                        else{
                            $buttonsItems[] = new MessageButton(
                                MessageButton::TYPE_POSTBACK,
                                $button['title'],
                                //CHATBO_NEW :TYPE        :FLOW_ID                      :FLOW_ITEM_UUID       :ITEM_UUID     :BUTTON_UUID
                                'CHATBO_NEW:BUTTON:'.$flowItem->getFlow()->getId().':'.$flowItem->getUuid().':'.$itemID.':'.$button['uuid']
                            );
                            $checkButton = true;
                        }
                    }
                    else{
                        return [
                            'result'=> false,
                            'message' => 'You cannot use Variables in JSON Growth Tool Opt-In message'
                        ];
                    }
                }
            }
        }

        return [
            'result'=> true,
            'buttonsItems'=>$buttonsItems,
            'checkButton'=>$checkButton
        ];
    }
}
