<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 17:48
 */

namespace AppBundle\Flows;

/**
 * Interface FlowsInterface
 * @package AppBundle\Flows
 */
interface FlowsInterface
{
    /**
     * @return mixed
     */
    public function sendStartStep();

    /**
     * Getting JSON Flow
     * @return mixed
     */
    public function getJSON();
}