<?php
/**
 * Created by PhpStorm.
 * Date: 11.12.18
 * Time: 17:36
 */

namespace AppBundle\Helper\Webhook;


use AppBundle\Entity\Conversation;
use AppBundle\Entity\ConversationMessages;
use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\SubscribersWidgets;
use AppBundle\Entity\UserRef;
use AppBundle\Entity\Widget;
use AppBundle\Flows\Flow;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\Subscriber\SubscriberActionHelper;
use Doctrine\ORM\EntityManager;
use Gos\Bundle\WebSocketBundle\Pusher\Zmq\ZmqPusher;
use pimax\Messages\Message;

class FbHelper
{
    /**
     * @param EntityManager $em
     * @param ZmqPusher $pusher
     * @param $pageID
     * @param Subscribers $subscriber
     * @param $messageConversationItems
     * @param int $type
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function saveConversation(EntityManager $em, ZmqPusher $pusher, $pageID, Subscribers $subscriber, $messageConversationItems, $type=1){
        $conversation = $em->getRepository("AppBundle:Conversation")->findOneBy(['page_id'=>$pageID, 'subscriber'=>$subscriber]);
        if(!$conversation instanceof Conversation){
            //Create Conversation
            $conversation = new Conversation($pageID, $subscriber);
            $em->persist($conversation);
            $em->flush();
        }

        //Create Conversation Message
        if(!empty($messageConversationItems)){
            $text = (isset($messageConversationItems['text'])) ? $messageConversationItems['text'] : null;
            $conversationMessage = new ConversationMessages($conversation, $messageConversationItems, $type, $text);
            $conversation->updateDateTime();
            $em->persist($conversation);
            $em->persist($conversationMessage);
            $em->flush();

            //PUSH SOCKET
            $pusher->push(['subscriberID'=>$subscriber->getId(), 'message' => $messageConversationItems], 'app_topic_chat', ['page_id' => $pageID]);
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param Widget $widget
     * @param Subscribers $subscriber
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public static function sendWidgetFlow(EntityManager $em, Page $page, Widget $widget, Subscribers $subscriber){
        if($widget->getStatus() == true){
            $widget->setOptIn($widget->getOptIn()+1);
            $em->persist($widget);
            $em->flush();

            //SUBSCRIBER SUBSCRIBE WIDGET
            $subscriberWidget = $em->getRepository("AppBundle:SubscribersWidgets")->findOneBy(['subscriber'=>$subscriber, 'widget'=>$widget]);
            if(!$subscriberWidget instanceof SubscribersWidgets){
                $subscriberWidget = new SubscribersWidgets($subscriber, $widget);
                $em->persist($subscriberWidget);
                $em->flush();
            }

            //SEND FLOW WIDGET
            if($widget->getFlow() instanceof Flows){
                $flowSend = new Flow($em, $widget->getFlow(), $subscriber);
                $flowSend->sendStartStep();
                //SUBSCRIBER SUBSCRIBE SEQUENCE
                if($widget->getSequence() instanceof Sequences){
                    SubscriberActionHelper::subscribeSequence($em, $page, $widget->getSequence()->getId(), [$subscriber->getId()]);
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param Widget $widget
     * @param $user_ref
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public static function sendWidgetFlowByUserRef(EntityManager $em, Page $page, Widget $widget, $user_ref){
        if($widget->getStatus() == true){
            $widget->setOptIn($widget->getOptIn()+1);
            $em->persist($widget);
            $em->flush();

            $flowSend = new Flow($em, $widget->getFlow(), $user_ref, Message::NOTIFY_REGULAR, $user_ref);
            $result = $flowSend->sendStartStep();
            if(is_array($result)){
                if(isset($result['fb_id']) && !empty($result['fb_id'])){
                    if(isset($result['recipient_id']) && !empty($result['recipient_id'])){
                        $bot = new MyFbBotApp($page->getAccessToken());
                        $user = $bot->userProfile($result['recipient_id'], 'first_name,last_name,profile_pic,locale,timezone,gender');
                        $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['subscriber_id'=>$result['recipient_id']]);
                        if(!$subscriber instanceof Subscribers){
                            $subscriber = new Subscribers($page->getPageId(),$result['recipient_id'],$user->getFirstName(),$user->getLastName(),$user->getGender(),$user->getLocale(),$user->getTimezone(),$user->getPicture());
                        }
                        else{
                            $subscriber->setAvatar($user->getPicture());
                        }
                        $em->persist($subscriber);
                        $em->flush();

                        //SUBSCRIBER SUBSCRIBE WIDGET
                        $subscriberWidget = $em->getRepository("AppBundle:SubscribersWidgets")->findOneBy(['subscriber'=>$subscriber, 'widget'=>$widget]);
                        if(!$subscriberWidget instanceof SubscribersWidgets){
                            $subscriberWidget = new SubscribersWidgets($subscriber, $widget);
                            $em->persist($subscriberWidget);
                            $em->flush();
                        }
                        //SUBSCRIBER SUBSCRIBE SEQUENCE
                        if($widget->getSequence() instanceof Sequences){
                            SubscriberActionHelper::subscribeSequence($em, $page, $widget->getSequence()->getId(), [$subscriber->getId()]);
                        }
                    }
                    else{
                        $userRef = new UserRef($user_ref, $widget);
                        $em->persist($userRef);
                        $em->flush();
                    }
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param FlowItems $flowItem
     * @param $itemID
     * @param $buttonID
     * @return null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function updateClickButtonForFlowItem(EntityManager $em, FlowItems $flowItem, $itemID, $buttonID){
        $nextStepID = null;
        //FIND BUTTONS
        $items = $flowItem->getItems();
        foreach ($items as $key=>$item){
            if(isset($item['uuid']) && $item['uuid'] == $itemID && isset($item['type'])){
                if ($item['type'] == 'card' || $item['type'] == 'gallery'){
                    if(isset($item['params']) && isset($item['params']['cards_array']) && !empty($item['params']['cards_array'])){
                        foreach ($item['params']['cards_array'] as $c_key=>$card){
                            if(isset($card['buttons']) && !empty($card['buttons'])){
                                foreach ($card['buttons'] as $b_key=>$button){
                                    if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                        if(isset($button['click']) && $button['click']>0){
                                            $items[$key]['params']['cards_array'][$c_key]['buttons'][$b_key]['click'] = $button['click'] + 1;
                                        }
                                        else{
                                            $items[$key]['params']['cards_array'][$c_key]['buttons'][$b_key]['click'] = 1;
                                        }
                                        $flowItem->setClicked($flowItem->getClicked() + 1);
                                        //GET NEXT STEP
                                        if(isset($button['next_step']) && !empty($button['next_step'])){
                                            $nextStepID = $button['next_step'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                elseif ($item['type'] == 'list'){
                    if(isset($item['params']['buttons']) && !empty($item['params']['buttons'])){
                        foreach ($item['params']['buttons'] as $b_key=>$button){
                            if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                if(isset($button['click']) && $button['click']>0){
                                    $items[$key]['params']['buttons'][$b_key]['click'] = $button['click'] + 1;
                                }
                                else{
                                    $items[$key]['params']['buttons'][$b_key]['click'] = 1;
                                }
                                $flowItem->setClicked($flowItem->getClicked() + 1);
                                //GET NEXT STEP
                                if(isset($button['next_step']) && !empty($button['next_step'])){
                                    $nextStepID = $button['next_step'];
                                }
                            }
                        }
                    }
                    if(isset($item['params']['list_array']) && !empty($item['params']['list_array'])){
                        foreach ($item['params']['list_array'] as $l_key=>$list){
                            if(isset($list['buttons']) && !empty($list['buttons'])){
                                foreach ($list['buttons'] as $b_key=>$button){
                                    if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                        if(isset($button['click']) && $button['click']>0){
                                            $items[$key]['params']['list_array'][$l_key]['buttons'][$b_key]['click'] = $button['click'] + 1;
                                        }
                                        else{
                                            $items[$key]['params']['list_array'][$l_key]['buttons'][$b_key]['click'] = 1;
                                        }
                                        $flowItem->setClicked($flowItem->getClicked() + 1);
                                        //GET NEXT STEP
                                        if(isset($button['next_step']) && !empty($button['next_step'])){
                                            $nextStepID = $button['next_step'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    if(isset($item['params']['buttons']) && !empty($item['params']['buttons'])){
                        foreach ($item['params']['buttons'] as $b_key=>$button){
                            if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                if(isset($button['click']) && $button['click']>0){
                                    $items[$key]['params']['buttons'][$b_key]['click'] = $button['click'] + 1;
                                }
                                else{
                                    $items[$key]['params']['buttons'][$b_key]['click'] = 1;
                                }
                                $flowItem->setClicked($flowItem->getClicked() + 1);
                                //GET NEXT STEP
                                if(isset($button['next_step']) && !empty($button['next_step'])){
                                    $nextStepID = $button['next_step'];
                                }
                            }
                        }
                    }
                    elseif (isset($item['buttons']) && !empty($item['buttons'])){
                        foreach ($item['buttons'] as $b_key=>$button){
                            if(isset($button['uuid']) && $button['uuid'] == $buttonID){
                                if(isset($button['click']) && $button['click']>0){
                                    $items[$key]['buttons'][$b_key]['click'] = $button['click'] + 1;
                                }
                                else{
                                    $items[$key]['buttons'][$b_key]['click'] = 1;
                                }
                                $flowItem->setClicked($flowItem->getClicked() + 1);
                                //GET NEXT STEP
                                if(isset($button['next_step']) && !empty($button['next_step'])){
                                    $nextStepID = $button['next_step'];
                                }
                            }
                        }
                    }
                }
            }
        }
        $flowItem->setItems($items);
        $em->persist($flowItem);
        $em->flush();

        return $nextStepID;
    }
}
