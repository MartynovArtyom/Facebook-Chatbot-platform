<?php
/**
 * Created by PhpStorm.
 * Date: 07.12.18
 * Time: 15:02
 */

namespace AppBundle\Helper\Subscriber;


use AppBundle\Entity\Conversation;
use AppBundle\Entity\CustomFields;
use AppBundle\Entity\Flows;
use AppBundle\Entity\Page;
use AppBundle\Entity\Sequences;
use AppBundle\Entity\SequencesItems;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\SubscribersCustomFields;
use AppBundle\Entity\SubscribersSequences;
use AppBundle\Entity\SubscribersTags;
use AppBundle\Entity\Tag;
use AppBundle\Flows\Flow;
use AppBundle\Flows\Util\TextVarReplacement;
use AppBundle\Helper\MyFbBotApp;
use AppBundle\Helper\Webhook\ZapierHelper;
use Doctrine\ORM\EntityManager;
use pimax\Messages\Message;
use Symfony\Component\HttpFoundation\Request;

class SubscriberActionHelper
{
    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $actions
     * @param Subscribers $subscriber
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public static function addActionForSubscriber(EntityManager $em, Page $page, $actions, Subscribers $subscriber){
        if(!empty($actions)){
            foreach ($actions as $item){
                switch ($item['type']){
                    //ADD TAG
                    case "add_tag":
                        if(isset($item['id']) && !empty($item['id'])){
                            self::addTag($em, $page, $item['id'], [$subscriber->getId()]);
                        }
                        break;
                    //REMOVE TAG
                    case "remove_tag":
                        if(isset($item['id']) && !empty($item['id'])){
                            self::removeTag($em, $page, $item['id'], [$subscriber->getId()]);
                        }
                        break;
                    //SUBSCRIBE SEQUENCE
                    case "subscribe_sequence":
                        if(isset($item['id']) && !empty($item['id'])){
                            self::subscribeSequence($em, $page, $item['id'], [$subscriber->getId()]);
                        }
                        break;
                    //UNSUBSCRIBE SEQUENCE
                    case "unsubscribe_sequence":
                        if(isset($item['id']) && !empty($item['id'])){
                            self::unSubscribeSequence($em, $page, $item['id'], [$subscriber->getId()]);
                        }
                        break;
                    //MARK CONVERSATION OPEN
                    case "mark_conversation_open":
                        self::markOpenConversation($em, $page, [$subscriber->getId()]);
                        break;
                    //NOTIFY ADMIN
                    case "notify_admins":
                        if(isset($item['team']) && !empty($item['team']) && isset($item['notificationText']) && !empty($item['notificationText'])){
                            $textVarReplacement = new TextVarReplacement();
                            $textNotify = $textVarReplacement->replaceTextVar($em, $item['notificationText'], $page, $subscriber);
                            $adminsIDs = [];
                            foreach ($item['team'] as $admin){
                                if(isset($admin['adminID']) && !empty($admin['adminID'])){
                                    $adminsIDs[] = $admin['adminID'];
                                }
                            }
                            if(!empty($adminsIDs)){
                                self::notifyAdmins($page, [$subscriber->getId()], $adminsIDs, $textNotify);
                            }
                        }
                        break;
                    //SET CUSTOM FIELD
                    case "set_custom_field":
                        if(isset($item['id']) && !empty($item['id'])){
                            if(isset($item['custom_field_option']) && isset($item['custom_field_option']['type']) && $item['custom_field_option']['type'] == 2){
                                if(isset($item['custom_field']) && isset($item['custom_field']['type'])){
                                    $value = null;
                                    $now = new \DateTime();
                                    if ($item['custom_field']['type'] == 3){
                                        $value = $now->format('Y-m-d');
                                    }
                                    elseif ($item['custom_field']['type'] == 4){
                                        $value = $now->format('Y-m-d H:i');
                                    }
                                    if(!is_null($value)){
                                        self::setCustomField($em, $page, $item['id'], [$subscriber->getId()], $value);
                                    }
                                }
                            }
                            else{
                                if(isset($item['value']) && !empty($item['value'])){
                                    self::setCustomField($em, $page, $item['id'], [$subscriber->getId()], $item['value']);
                                }
                            }
                        }
                        break;
                    //CLEAR CUSTOM FIELD
                    case "clear_subscriber_custom_field":
                        if(isset($item['id']) && !empty($item['id'])){
                            self::clearCustomField($em, $page, $item['id'], [$subscriber->getId()]);
                        }
                        break;
                    //SUBSCRIBE BOT
                    case "subscribe_bot":
                        self::subscribeBot($em, $page, [$subscriber->getId()]);
                        break;
                    //UNSUBSCRIBE BOT
                    case "unsubscribe_bot":
                        self::unSubscribeBot($em, $page, [$subscriber->getId()]);
                        break;
                    //ROMOVE FORM BOR
                    case "remove_bot":
                        self::removeFromBot($em, $page, [$subscriber->getId()]);
                        break;
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $tagID
     * @param $subscriberIDs
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function addTag(EntityManager $em, Page $page, $tagID, $subscriberIDs){
        $tag = $em->getRepository("AppBundle:Tag")->findOneBy(['id'=>$tagID, 'page_id'=>$page->getPageId()]);
        $counter = 0;
        if($tag instanceof Tag){
            if(!empty($subscriberIDs) && is_array($subscriberIDs)){
                foreach ($subscriberIDs as $subscriberID){
                    $subscriber = $em->getRepository("AppBundle:Subscribers")->find($subscriberID);
                    if($subscriber instanceof Subscribers){
                        $subscriberTag = $em->getRepository("AppBundle:SubscribersTags")->findOneBy(['subscriber'=>$subscriber,'tag'=>$tag]);
                        if(!$subscriberTag instanceof SubscribersTags){
                            $subscriberTag = new SubscribersTags($subscriber, $tag);
                            $em->persist($subscriberTag);
                            $em->flush();
                            $counter++;

                            //ZAPIER TRIGGER
                            ZapierHelper::triggerNewTag($em, $page, $subscriberTag);
                        }
                    }
                }
            }
        }

        return $counter;
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $tagID
     * @param $subscriberIDs
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function removeTag(EntityManager $em, Page $page, $tagID, $subscriberIDs){
        $tag = $em->getRepository("AppBundle:Tag")->findOneBy(['id'=>$tagID, 'page_id'=>$page->getPageId()]);
        $counter = 0;
        if($tag instanceof Tag){
            if(!empty($subscriberIDs) && is_array($subscriberIDs)){
                foreach ($subscriberIDs as $subscriberID){
                    $subscriber = $em->getRepository("AppBundle:Subscribers")->find($subscriberID);
                    if($subscriber instanceof Subscribers){
                        $subscriberTag = $em->getRepository("AppBundle:SubscribersTags")->findOneBy(['subscriber'=>$subscriber, 'tag'=>$tag]);
                        if($subscriberTag instanceof SubscribersTags){
                            $em->remove($subscriberTag);
                            $em->flush();
                            $counter++;
                        }
                    }
                }
            }
        }

        return $counter;
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $sequenceID
     * @param $subscriberIDs
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public static function subscribeSequence(EntityManager $em, Page $page, $sequenceID, $subscriberIDs){
        $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['id'=>$sequenceID, 'page_id'=>$page->getPageId()]);
        $counter = 0;
        if($sequence instanceof Sequences){
            if(!empty($subscriberIDs) && is_array($subscriberIDs)){
                foreach ($subscriberIDs as $subscriberID){
                    $subscriber = $em->getRepository("AppBundle:Subscribers")->find($subscriberID);
                    if($subscriber instanceof Subscribers){
                        $subscriberSequence = $em->getRepository("AppBundle:SubscribersSequences")->findOneBy(['subscriber'=>$subscriber,'sequence'=>$sequence]);
                        if(!$subscriberSequence instanceof SubscribersSequences){
                            $subscriberSequence = new SubscribersSequences($subscriber, $sequence);
                            $em->persist($subscriberSequence);
                            $em->flush();
                            $counter++;
                            self::sendSequenceImmediately($em, $subscriberSequence);

                            //ZAPIER TRIGGER
                            ZapierHelper::triggerSubscriberToSequence($em, $page, $subscriberSequence);
                        }
                    }
                }
            }
        }

        return $counter;
    }

    /**
     * @param EntityManager $em
     * @param SubscribersSequences $subscriberSequence
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public static function sendSequenceImmediately(EntityManager $em, SubscribersSequences $subscriberSequence){
        if($subscriberSequence->getSequence() instanceof Sequences && $subscriberSequence->getSubscriber() instanceof Subscribers && $subscriberSequence->getSubscriber()->getStatus() == true){
            $sequenceItem = $em->getRepository("AppBundle:SequencesItems")->findOneBy(['sequence'=>$subscriberSequence->getSequence(), 'number'=>$subscriberSequence->getStage()]);
            if(!$sequenceItem instanceof SequencesItems || $sequenceItem->getStatus() == false) {
                $sequenceItem = $em->getRepository("AppBundle:SequencesItems")->getNextSequencesItem($subscriberSequence->getSequence(), $subscriberSequence->getStage());
            }
            if ($sequenceItem instanceof SequencesItems && $sequenceItem->getFlow() instanceof Flows) {
                $delay = $sequenceItem->getDelay();
                if(isset($delay['type'])){
                    if($delay['type'] == 'immediately'){
                        $subscriberSequence->setProcessed(true);
                        $em->persist($subscriberSequence);
                        $em->flush();
                        $flowSend = new Flow($em, $sequenceItem->getFlow(), $subscriberSequence->getSubscriber());
                        $flowSend->sendStartStep();

                        $subscriberSequence->setStage($sequenceItem->getNumber()+1);
                        $subscriberSequence->setLastSendDate(new \DateTime());
                        $subscriberSequence->setProcessed(false);
                        $em->persist($subscriberSequence);
                        $em->flush();

                        self::sendSequenceImmediately($em, $subscriberSequence);
                    }
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $sequenceID
     * @param $subscriberIDs
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function unSubscribeSequence(EntityManager $em, Page $page, $sequenceID, $subscriberIDs){
        $sequence = $em->getRepository("AppBundle:Sequences")->findOneBy(['id'=>$sequenceID, 'page_id'=>$page->getPageId()]);
        $counter = 0;
        if($sequence instanceof Sequences){
            if(!empty($subscriberIDs) && is_array($subscriberIDs)){
                foreach ($subscriberIDs as $subscriberID){
                    $subscriber = $em->getRepository("AppBundle:Subscribers")->find($subscriberID);
                    if($subscriber instanceof Subscribers){
                        $subscriberSequence = $em->getRepository("AppBundle:SubscribersSequences")->findOneBy(['subscriber'=>$subscriber,'sequence'=>$sequence]);
                        if($subscriberSequence instanceof SubscribersSequences){
                            $em->remove($subscriberSequence);
                            $em->flush();
                            $counter++;
                        }
                    }
                }
            }
        }

        return $counter;
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $subscriberIDs
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function markOpenConversation(EntityManager $em, Page $page, $subscriberIDs){
        if(!empty($subscriberIDs) && is_array($subscriberIDs)){
            foreach ($subscriberIDs as $subscriberID){
                $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$subscriberID]);
                if($subscriber instanceof Subscribers){
                    $needTrigger = false;
                    $conversation = $em->getRepository("AppBundle:Conversation")->findOneBy(['page_id'=>$page->getPageId(), 'subscriber'=>$subscriber]);
                    if(!$conversation instanceof Conversation){
                        $conversation = new Conversation($page->getPageId(), $subscriber);
                    }
                    else{
                        if($conversation->getStatus() == false){
                            $needTrigger = true;
                        }
                    }
                    $conversation->setStatus(true);
                    $em->persist($conversation);
                    $em->flush();

                    if($needTrigger == true){
                        //ZAPIER TRIGGER
                        ZapierHelper::triggerChatOpen($em, $page, $subscriber);
                    }
                }
            }
        }
    }

    /**
     * @param Page $page
     * @param $subscriberIDs
     * @param $adminIDs
     * @param $textNotify
     */
    public static function notifyAdmins(Page $page, $subscriberIDs, $adminIDs, $textNotify){
        try{
            $data = [
                "pageID" => $page->getPageId(),
                "subscriberIDs" => $subscriberIDs,
                "adminIDs" => $adminIDs,
                "textNotify" => $textNotify,
            ];
            $request = Request::createFromGlobals();
            $url = $request->getSchemeAndHttpHost()."/v2/fetch/action/send_notify_admin";

            $headers = [
                'Content-Type: application/json',
            ];

            $process = curl_init($url);
            curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($process, CURLOPT_HEADER, false);
            curl_setopt($process, CURLOPT_TIMEOUT, 10);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
            $return = curl_exec($process);
            curl_close($process);
        }
        catch (\Exception $e){

        }

    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $customFieldID
     * @param $subscriberIDs
     * @param $value
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function setCustomField(EntityManager $em, Page $page, $customFieldID, $subscriberIDs, $value){
        $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['id'=>$customFieldID, 'page_id'=>$page->getPageId()]);
        if($customField instanceof CustomFields){
            if(!empty($value)) {
                if (!empty($subscriberIDs) && is_array($subscriberIDs)) {
                    foreach ($subscriberIDs as $subscriberID) {
                        $subscriber = $em->getRepository("AppBundle:Subscribers")->find($subscriberID);
                        if ($subscriber instanceof Subscribers) {
                            $subscriberCustomField = $em->getRepository("AppBundle:SubscribersCustomFields")->findOneBy([
                                'subscriber' => $subscriber,
                                'customField' => $customField
                            ]);
                            if ($subscriberCustomField instanceof SubscribersCustomFields) {
                                $subscriberCustomField->setValue($value);
                            } else {
                                $subscriberCustomField = new SubscribersCustomFields($subscriber, $customField, $value);
                            }

                            $em->persist($subscriberCustomField);
                            $em->flush();

                            //ZAPIER TRIGGER
                            ZapierHelper::triggerSetCustomField($em, $page, $subscriberCustomField);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $customFieldID
     * @param $subscriberIDs
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function clearCustomField(EntityManager $em, Page $page, $customFieldID, $subscriberIDs){
        $customField = $em->getRepository("AppBundle:CustomFields")->findOneBy(['id'=>$customFieldID, 'page_id'=>$page->getPageId()]);
        if($customField instanceof CustomFields){
            if(!empty($subscriberIDs) && is_array($subscriberIDs)){
                foreach ($subscriberIDs as $subscriberID){
                    $subscriber = $em->getRepository("AppBundle:Subscribers")->find($subscriberID);
                    if($subscriber instanceof Subscribers){
                        $subscriberCustomField = $em->getRepository("AppBundle:SubscribersCustomFields")->findOneBy([
                            'subscriber'=>$subscriber,
                            'customField'=>$customField
                        ]);
                        if($subscriberCustomField instanceof SubscribersCustomFields){
                            $em->remove($subscriberCustomField);
                            $em->flush();
                        }
                    }
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $subscriberIDs
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function subscribeBot(EntityManager $em, Page $page, $subscriberIDs){
        if(!empty($subscriberIDs) && is_array($subscriberIDs)){
            foreach ($subscriberIDs as $subscriberID){
                $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$subscriberID]);
                if($subscriber instanceof Subscribers){
                    $subscriber->setStatus(true);
                    $em->persist($subscriber);
                    $em->flush();
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $subscriberIDs
     * @return int
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function unSubscribeBot(EntityManager $em, Page $page, $subscriberIDs){
        $counter = 0;
        if(!empty($subscriberIDs) && is_array($subscriberIDs)){
            foreach ($subscriberIDs as $subscriberID){
                $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$subscriberID]);
                if($subscriber instanceof Subscribers){
                    if($subscriber->getStatus() == true){
                        $counter++;
                    }
                    $subscriber->setStatus(false);
                    $em->persist($subscriber);
                    $em->flush();
                }
            }
        }

        return $counter;
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $subscriberIDs
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public static function removeFromBot(EntityManager $em, Page $page, $subscriberIDs){
        $counterAll = 0;
        $counterSubscriber = 0;
        $counterUnSubscriber = 0;
        if(!empty($subscriberIDs) && is_array($subscriberIDs)){
            foreach ($subscriberIDs as $subscriberID){
                $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$subscriberID]);
                if($subscriber instanceof Subscribers){
                    $counterAll++;
                    if($subscriber->getStatus() == true){
                        $counterSubscriber++;
                    }
                    else{
                        $counterUnSubscriber++;
                    }
                    $em->remove($subscriber);
                    $em->flush();
                }
            }
        }

        return ['counterAll'=>$counterAll, 'counterSubscriber'=>$counterSubscriber,'counterUnSubscriber'=>$counterUnSubscriber];
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param $subscriberIDs
     * @return array
     */
    public static function exportToPSID(EntityManager $em, Page $page, $subscriberIDs){
        $psid = [];
        if(!empty($subscriberIDs) && is_array($subscriberIDs)){
            foreach ($subscriberIDs as $subscriberID){
                $subscriber = $em->getRepository("AppBundle:Subscribers")->findOneBy(['page_id'=>$page->getPageId(), 'id'=>$subscriberID]);
                if($subscriber instanceof Subscribers){
                    $psid[] = ['psid'=>$subscriber->getSubscriberId()];
                }
            }
        }

        return $psid;
    }

    /**
     * @param Page $page
     * @param Subscribers $subscriber
     * @param $text
     * @return array
     */
    public static function sendMessageText(Page $page, Subscribers $subscriber, $text){
        $bot = new MyFbBotApp($page->getAccessToken());
        $sendItem = new Message($subscriber->getSubscriberId(), $text);
        $result_send = $bot->send($sendItem);

        return $result_send;
    }

}
