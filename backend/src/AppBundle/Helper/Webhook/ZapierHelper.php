<?php
/**
 * Created by PhpStorm.
 * Date: 18.01.19
 * Time: 16:35
 */

namespace AppBundle\Helper\Webhook;


use AppBundle\Entity\CustomFields;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\SubscribersCustomFields;
use AppBundle\Entity\SubscribersSequences;
use AppBundle\Entity\SubscribersTags;
use AppBundle\Entity\ZapierWebhook;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;

class ZapierHelper
{
    /**
     * @param EntityManager $em
     * @param Page $page
     * @param Subscribers $subscriber
     * @return array
     */
    public static function generateUserResponse(EntityManager $em, Page $page, Subscribers $subscriber){
        $customFieldsList = $em->getRepository("AppBundle:CustomFields")->findBy(['page_id'=>$page->getPageId(), 'status'=>true]);
        $customFields = [];
        if(!empty($customFieldsList)){
            foreach ($customFieldsList as $customField){
                if($customField instanceof CustomFields){
                    $subscriberCustomField = $em->getRepository("AppBundle:SubscribersCustomFields")->findOneBy(['subscriber'=>$subscriber, 'customField'=>$customField]);
                    $value = null;
                    if($subscriberCustomField instanceof SubscribersCustomFields){
                        $value = $subscriberCustomField->getValue();
                    }
                    $customFields[$customField->getName()] = $value;
                }
            }
        }
        return [
            'page_id' => $subscriber->getPageId(),
            'user_id' => $subscriber->getSubscriberId(),
            'first_name' => $subscriber->getFirstName(),
            'last_name' => $subscriber->getLastName(),
            'full_name' => $subscriber->getFirstName()." ".$subscriber->getLastName(),
            'gender' => $subscriber->getGender(),
            'locale' => $subscriber->getLocale(),
            'timezone' => $subscriber->getTimezone(),
            'avatar' => $subscriber->getAvatar(),
            'last_interaction' => $subscriber->getLastInteraction(),
            'date_subscribed' => $subscriber->getDateSubscribed(),
            'status' => $subscriber->getStatus(),
            'custom_fields' => $customFields
        ];
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param Subscribers $subscriber
     */
    public static function triggerNewSubscriber(EntityManager $em, Page $page, Subscribers $subscriber){
        $zapierWebhooks = $em->getRepository("AppBundle:ZapierWebhook")->findBy(['page_id'=>$page->getPageId(), 'event'=>'new_subscriber']);
        if(!empty($zapierWebhooks)){
            foreach ($zapierWebhooks as $zapierWebhook){
                if($zapierWebhook instanceof ZapierWebhook){
                    $result = [
                        'user' => self::generateUserResponse($em, $page, $subscriber)
                    ];
                    self::sendHook($zapierWebhook->getTargetUrl(), [$result]);
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param SubscribersCustomFields $subscribersCustomField
     */
    public static function triggerSetCustomField(EntityManager $em, Page $page, SubscribersCustomFields $subscribersCustomField){
        $zapierWebhooks = $em->getRepository("AppBundle:ZapierWebhook")->findBy(['page_id'=>$page->getPageId(), 'event'=>'set_custom_field', 'field_id'=>$subscribersCustomField->getCustomField()->getId()]);
        if(!empty($zapierWebhooks)){
            foreach ($zapierWebhooks as $zapierWebhook){
                if($zapierWebhook instanceof ZapierWebhook){
                    $result = [
                        'field_value' => $subscribersCustomField->getValue(),
                        'custom_field' => [
                            'id' => $subscribersCustomField->getCustomField()->getId(),
                            'name' => $subscribersCustomField->getCustomField()->getName(),
                            'type' => $subscribersCustomField->getCustomField()->getType(),
                            'description' => $subscribersCustomField->getCustomField()->getDescription()
                        ],
                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscribersCustomField->getSubscriber())
                    ];
                    self::sendHook($zapierWebhook->getTargetUrl(), [$result]);
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param SubscribersTags $subscribersTag
     */
    public static function triggerNewTag(EntityManager $em, Page $page, SubscribersTags $subscribersTag){
        $zapierWebhooks = $em->getRepository("AppBundle:ZapierWebhook")->findBy(['page_id'=>$page->getPageId(), 'event'=>'new_tag', 'field_id'=>$subscribersTag->getTag()->getId()]);
        if(!empty($zapierWebhooks)){
            foreach ($zapierWebhooks as $zapierWebhook){
                if($zapierWebhook instanceof ZapierWebhook){
                    $result = [
                        'tag' => [
                            'id' => $subscribersTag->getTag()->getId(),
                            'name' => $subscribersTag->getTag()->getName()
                        ],
                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscribersTag->getSubscriber())
                    ];
                    self::sendHook($zapierWebhook->getTargetUrl(), [$result]);
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param SubscribersSequences $subscribersSequence
     */
    public static function triggerSubscriberToSequence(EntityManager $em, Page $page, SubscribersSequences $subscribersSequence){
        $zapierWebhooks = $em->getRepository("AppBundle:ZapierWebhook")->findBy(['page_id'=>$page->getPageId(), 'event'=>'subscribe_sequence', 'field_id'=>$subscribersSequence->getSequence()->getId()]);
        if(!empty($zapierWebhooks)){
            foreach ($zapierWebhooks as $zapierWebhook){
                if($zapierWebhook instanceof ZapierWebhook){
                    $result = [
                        'sequence' => [
                            'id' => $subscribersSequence->getSequence()->getId(),
                            'title' => $subscribersSequence->getSequence()->getTitle()
                        ],
                        'user' => ZapierHelper::generateUserResponse($em, $page, $subscribersSequence->getSubscriber())
                    ];
                    self::sendHook($zapierWebhook->getTargetUrl(), [$result]);
                }
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param Page $page
     * @param Subscribers $subscriber
     */
    public static function triggerChatOpen(EntityManager $em, Page $page, Subscribers $subscriber){
        $zapierWebhooks = $em->getRepository("AppBundle:ZapierWebhook")->findBy(['page_id'=>$page->getPageId(), 'event'=>'open_chat']);
        if(!empty($zapierWebhooks)){
            foreach ($zapierWebhooks as $zapierWebhook){
                if($zapierWebhook instanceof ZapierWebhook){
                    $result = [
                        'user' => self::generateUserResponse($em, $page, $subscriber)
                    ];
                    self::sendHook($zapierWebhook->getTargetUrl(), [$result]);
                }
            }
        }
    }

    /**
     * @param $unique_path
     * @param $data
     */
    public static function sendHook($unique_path, $data){
        try{
            $headers = [
                'Content-Type: application/json',
            ];

            $process = curl_init($unique_path);
            curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($process, CURLOPT_HEADER, false);
            curl_setopt($process, CURLOPT_TIMEOUT, 20);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
            $return = curl_exec($process);
            curl_close($process);
        }
        catch (\Exception $e){
            $fs = new Filesystem();
            $fs->appendToFile('zapier_request.txt',"SEND HOOK ERROR \n\n");
            $fs->appendToFile('zapier_request.txt', $e->getMessage()."\n\n");
        }
    }
}