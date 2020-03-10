<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 17:00
 */

namespace AppBundle\Flows\Type\SendMessage;

/**
 * Interface SendMessageInterface
 * @package AppBundle\Flows\Type\SendMessage
 */
interface SendMessageInterface
{
    /**
     * Gets data for send
     *
     * @return mixed|void
     */
    public function getSendData();

    /**
     * @return mixed
     */
    public function getJSONData();
}