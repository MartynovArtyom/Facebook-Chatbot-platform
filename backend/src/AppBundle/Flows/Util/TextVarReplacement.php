<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 17:14
 */

namespace AppBundle\Flows\Util;


use AppBundle\Entity\CustomFields;
use AppBundle\Entity\Page;
use AppBundle\Entity\Subscribers;
use AppBundle\Entity\SubscribersCustomFields;
use Doctrine\ORM\EntityManager;
use pimax\UserProfile;

/**
 * Class TextVarReplacement
 * @package AppBundle\Flows\Util
 */
class TextVarReplacement implements TextVarReplacementInterface
{
    /**
     * @param EntityManager $em
     * @param $textMessage
     * @param Page $page
     * @param array|null|string $subscriber
     * @return mixed
     * @throws
     */
    public function replaceTextVar(EntityManager $em, $textMessage, Page $page, $subscriber){
        $textMessage = str_replace('{{page_id}}', $page->getPageId(), $textMessage);
        $textMessage = str_replace('{{page_name}}', $page->getTitle(), $textMessage);
        if($subscriber instanceof Subscribers){
            $textMessage = str_replace('{{user_id}}', $subscriber->getSubscriberId(), $textMessage);
            $textMessage = str_replace('{{user_first_name}}', $subscriber->getFirstName(), $textMessage);
            $textMessage = str_replace('{{user_last_name}}', $subscriber->getLastName(), $textMessage);
            $textMessage = str_replace('{{user_full_name}}', $subscriber->getFirstName().' '.$subscriber->getLastName(), $textMessage);
            $textMessage = str_replace('{{user_gender}}', ucfirst($subscriber->getGender()), $textMessage);
            $locale = $language = '';
            if(!empty($subscriber->getLocale())){
                $locale = locale_get_display_name($subscriber->getLocale());
                $language = locale_get_display_language($subscriber->getLocale());
            }
            $textMessage = str_replace('{{user_locale}}', ucfirst($locale), $textMessage);
            $textMessage = str_replace('{{user_language}}', ucfirst($language), $textMessage);
            //PARSE CUSTOM FIELDS
            $customFieldsArray = [];
            preg_match_all('/[{]{2}[cf_]+\d+[}]{2}/', $textMessage, $customFieldsArray);
            if(!empty($customFieldsArray) && isset($customFieldsArray[0])){
                foreach ($customFieldsArray[0] as $customFieldItem){
                    $value = '';
                    $customFieldID = substr($customFieldItem, 5, -2);
                    $customField = $em->getRepository("AppBundle:CustomFields")->find($customFieldID);
                    if($customField instanceof CustomFields){
                        $customFieldSubscriber = $em->getRepository("AppBundle:SubscribersCustomFields")->findOneBy(['customField'=>$customField,'subscriber'=>$subscriber]);
                        if($customFieldSubscriber instanceof SubscribersCustomFields){
                            if($customField->getType() == 3 || $customField->getType() == 4){
                                $date = new \DateTime($customFieldSubscriber->getValue());
                                if($date instanceof \DateTime){
                                    if($customField->getType() == 3){
                                        $value = $date->format('Y-m-d');
                                    }
                                    else{
                                        $value = $date->format('Y-m-d H:i');
                                    }
                                }
                                else{
                                    $value = $customFieldSubscriber->getValue();
                                }
                            }
                            elseif ($customField->getType() == 5){
                                if($customFieldSubscriber->getValue() == true || $customFieldSubscriber->getValue() == "true"){
                                    $value = "Yes";
                                }
                                elseif ($customFieldSubscriber->getValue() == false || $customFieldSubscriber->getValue() == "false"){
                                    $value = "No";
                                }
                                else{
                                    $value = $customFieldSubscriber->getValue();
                                }
                            }
                            else{
                                $value = $customFieldSubscriber->getValue();
                            }
                        }
                    }
                    $textMessage = str_replace($customFieldItem, $value, $textMessage);
                }
            }
        }
        elseif($subscriber instanceof UserProfile){
            $textMessage = str_replace('{{user_id}}', '', $textMessage);
            $textMessage = str_replace('{{user_first_name}}', $subscriber->getFirstName(), $textMessage);
            $textMessage = str_replace('{{user_last_name}}', $subscriber->getLastName(), $textMessage);
            $textMessage = str_replace('{{user_full_name}}', $subscriber->getFirstName()." ".$subscriber->getLastName(), $textMessage);
            $textMessage = str_replace('{{user_gender}}', $subscriber->getGender(), $textMessage);
            $locale = $language = '';
            if(!empty($subscriber->getLocale())){
                $locale = locale_get_display_name($subscriber->getLocale());
                $language = locale_get_display_language($subscriber->getLocale());
            }
            $textMessage = str_replace('{{user_locale}}', ucfirst($locale), $textMessage);
            $textMessage = str_replace('{{user_language}}', ucfirst($language), $textMessage);
            $textMessage = preg_replace('/[{]{2}[cf_]+\d+[}]{2}/', '', $textMessage);
        }
        else{
            $textMessage = str_replace('{{user_id}}', '', $textMessage);
            $textMessage = str_replace('{{user_first_name}}', '', $textMessage);
            $textMessage = str_replace('{{user_last_name}}', '', $textMessage);
            $textMessage = str_replace('{{user_full_name}}', '', $textMessage);
            $textMessage = str_replace('{{user_gender}}', '', $textMessage);
            $textMessage = str_replace('{{user_locale}}', '', $textMessage);
            $textMessage = str_replace('{{user_language}}', '', $textMessage);
            $textMessage = preg_replace('/[{]{2}[cf_]+\d+[}]{2}/', '', $textMessage);
        }

        return $textMessage;
    }

    /**
     * Check Text Var
     *
     * @param $textMessage
     * @return bool
     */
    public function checkTextVar($textMessage){
        if (preg_match("/[{]{2}+[\w]+[}]{2}/", $textMessage)) {
            return false;
        } else {
            return true;
        }
    }
}
