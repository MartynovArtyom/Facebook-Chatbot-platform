<?php


namespace AppBundle\Webhooks;


use AppBundle\Entity\Widget;

interface FBFeedInterface
{
    /**
     * @return mixed
     */
    public function handler();

    /**
     * @param Widget $widget
     * @param $commentId
     * @param $options
     * @param $recipient
     * @return mixed
     */
    public function sendPrivateReply(Widget $widget, $commentId, $options, $recipient);
}
