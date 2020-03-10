<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 12:41
 */

namespace AppBundle\Flows\Type;

/**
 * Interface FlowsTypeInterface
 * @package AppBundle\Flows\Type
 */
interface FlowsTypeInterface
{
    /**
     * @return array
     */
    public function send();

    /**
     * @return mixed
     */
    public function getJSON();
}