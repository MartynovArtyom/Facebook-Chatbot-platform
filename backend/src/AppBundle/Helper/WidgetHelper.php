<?php


namespace AppBundle\Helper;


use AppBundle\Entity\FlowItems;
use AppBundle\Entity\Widget;
use AppBundle\Helper\Flow\FlowHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;

class WidgetHelper
{

    /**
     * @param Widget $widget
     * @param $page_id
     */
    public static function generateWidgetFile(Widget $widget, $page_id){
        if(in_array($widget->getType(),[1,2,3,4,5,6,10])){
            $str = "var data_chatbo_".$widget->getStringType()."={\n";
            foreach($widget->getOptions() as $key=>$item) {
                if(is_bool($item)){
                    if($item == true){
                        $item = 1;
                    }
                    else{
                        $item = 0;
                    }
                }
                $str .= "\t".$key.":'".addslashes($item)."',\n";
            }
            $str .= "\twidget_id:'".addslashes($widget->getId())."',\n";
            $str .= "\tpage_id:'".addslashes($page_id)."',\n";
            $status = 0;
            if($widget->getStatus() == true){
                $status = 1;
            }
            $str .= "\tstatus:'".addslashes($status)."'";
            $str .= "\n};\n\n";
            $fs = new Filesystem();
            $fs->dumpFile('widget/'.$page_id.'/'.$widget->getId().'.js', $str);

            if(!empty($widget->getStringType())){
                $origin = file_get_contents('widget/original/js/'.$widget->getStringType().'.js');
                $fs->appendToFile('widget/'.$page_id.'/'.$widget->getId().'.js', $origin);
            }
        }
    }

    /**
     * @param EntityManager $em
     * @param $widgets
     * @return array
     */
    public static function generateWidgetsResponse(EntityManager $em, $widgets){
        $result = [];
        if(!empty($widgets)){
            foreach ($widgets as $widget){
                if($widget instanceof Widget){
                    $result[] = self::generateWidgetResponse($em, $widget);
                }
            }
        }

        return $result;
    }

    /**
     * @param EntityManager $em
     * @param Widget $widget
     * @return array
     */
    public static function generateWidgetResponse(EntityManager $em, Widget $widget){
        $flowStartItem = $em->getRepository("AppBundle:FlowItems")->findOneBy(['flow'=>$widget->getFlow(), 'startStep'=>true]);

        return [
            'id' => $widget->getId(),
            'name' => $widget->getName(),
            'type' => $widget->getType(),
            'status' => $widget->getStatus(),
            'flow' => FlowHelper::getFlowResponse($em, $widget->getFlow()),
            'shows' => $widget->getShows(),
            'optIn' => $widget->getOptIn(),
            'sent' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getSent() : 0,
            'delivered' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getDelivered() : 0,
            'opened' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getOpened() : 0,
            'clicked' => ($flowStartItem instanceof FlowItems) ? $flowStartItem->getClicked() : 0
        ];
    }
}
