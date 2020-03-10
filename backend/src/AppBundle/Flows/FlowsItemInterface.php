<?php
/**
 * Created by PhpStorm.
 * Date: 30.01.19
 * Time: 17:35
 */

namespace AppBundle\Flows;

/**
 * Interface FlowsItemInterface
 * @package AppBundle\Flows
 */
interface FlowsItemInterface
{
    /**
     * Send Flow Item
     *
     * @return mixed
     */
    public function send();

    /**
     * Generate Flows Item Object
     *
     * @return mixed
     */
    public function generateFlowsItemObject();

    /**
     * Send FLows Item Object
     *
     * @return mixed
     */
    public function sendFlowsItemObject();

    /**
     * Check and send next step flow item
     *
     * @return mixed
     */
    public function checkNextStepFlowItem();

    /**
     * Get JSON Flow Item
     *
     * @return mixed
     */
    public function getJSON();

    /**
     * Get JSON Flow Item Object
     *
     * @return mixed
     */
    public function getJSONFlowsItemObject();
}