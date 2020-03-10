<?php
/**
 * Created by PhpStorm.
 * Date: 29.01.19
 * Time: 17:11
 */

namespace AppBundle\Flows\Util;


use AppBundle\Entity\Page;
use Doctrine\ORM\EntityManager;

/**
 * Interface TextVarReplacementInterface
 * @package AppBundle\Flows\Util
 */
interface TextVarReplacementInterface
{
    /**
     * Replace Text Var
     *
     * @param EntityManager $em
     * @param $textMessage
     * @param Page $page
     * @param string|array|null $subscriber
     * @return mixed
     */
    public function replaceTextVar(EntityManager $em, $textMessage, Page $page, $subscriber);

    /**
     * Check Text Var
     *
     * @param $textMessage
     * @return mixed
     */
    public function checkTextVar($textMessage);
}