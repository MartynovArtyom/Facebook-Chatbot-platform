<?php


namespace AppBundle\Flows;


use AppBundle\Entity\Flows;

/**
 * Interface CopyFlowInterface
 * @package AppBundle\Flows
 */
interface CopyFlowInterface
{
    /**
     * @param int $flowType
     * @return mixed
     */
    public function copy($flowType = Flows::FLOW_TYPE_CONTENT);
}
