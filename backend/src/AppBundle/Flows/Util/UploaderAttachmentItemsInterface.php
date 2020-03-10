<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 11:42
 */

namespace AppBundle\Flows\Util;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;

/**
 * Interface UploaderAttachmentItemsInterface
 * @package AppBundle\Flows\Util
 */
interface UploaderAttachmentItemsInterface
{
    /**
     * @param EntityManager $em
     * @param Page $page
     * @param FlowItems $flowItem
     * @return mixed
     */
    public function upload(EntityManager $em, Page $page, FlowItems $flowItem);
}