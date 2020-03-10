<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 17:30
 */

namespace AppBundle\Flows\Util;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;

/**
 * Interface QuickReplyGeneratorInterface
 * @package AppBundle\Flows\Util
 */
interface QuickReplyGeneratorInterface
{
    /**
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param $subscriber
     * @return mixed
     */
    public function generateQuickReplyItems(EntityManager $em, Page $page, FlowItems $flowItem, $subscriber);

    /**
     * @param FlowItems $flowItem
     * @return mixed
     */
    public function generateJSONQuickReplyItems(FlowItems $flowItem);
}