<?php
/**
 * Created by PhpStorm.
 * Date: 13.03.19
 * Time: 14:36
 */

namespace AppBundle\Webhooks;


use pimax\UserProfile;

interface FBInterface
{
    /**
     * @return mixed
     */
    public function handler();

    /**
     * @param $message
     * @return mixed
     */
    public function acceptMessage($message);

    /**
     * @param $message
     * @return mixed
     */
    public function echoMessage($message);

    /**
     * @param $message
     * @return mixed
     */
    public function widgetSendMessageUserRef($message);

    /**
     * @param $message
     * @return mixed
     */
    public function deliveredMessage($message);

    /**
     * @param $message
     * @return mixed
     */
    public function readMessage($message);

    /**
     * @param $message
     * @param UserProfile $user
     */
    public function createSubscriber($message, UserProfile $user);

    /**
     * @param UserProfile $user
     */
    public function updateSubscriberInfo(UserProfile $user);

    /**
     * @param $message
     */
    public function checkboxPluginStatistics($message);

    /**
     * @param $message
     * @return mixed
     */
    public function pluginSendToMessenger($message);

    /**
     * @param $message
     * @return mixed
     */
    public function pluginRefUrl($message);

    /**
     * @param $message
     * @return mixed
     */
    public function payloadPostback($message);

    /**
     * @param $message
     * @return mixed
     */
    public function payloadQuickReply($message);

    /**
     * @param $message
     * @return mixed
     */
    public function textReply($message);
}