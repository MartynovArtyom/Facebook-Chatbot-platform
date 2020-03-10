<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 17:22
 */

namespace AppBundle\Flows\Util;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;

/**
 * Interface ButtonGeneratorInterface
 * @package AppBundle\Flows\Util
 */
interface ButtonGeneratorInterface
{
    /**
     * Generate button items
     *
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @param $item
     * @param $subscriber
     * @param $itemID
     * @return mixed
     */
    public function generateButtonItems(EntityManager $em, Page $page, FlowItems $flowItem, $item, $subscriber, $itemID);

    /**
     * Generate button items for json
     *
     * @param FlowItems $flowItem
     * @param $item
     * @param $itemID
     * @return mixed
     */
    public function generateJSONButtonItems(FlowItems $flowItem, $item, $itemID);
}